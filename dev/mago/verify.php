<?php

declare(strict_types=1);

namespace aodihis\craftlangfuse\Dev\Mago;

use RuntimeException;

const EXPECTED = [
    'violations/src/ImportsObserverSdk.php' => ['craft/forbidden-imports' => 2],
    'violations/src/NoisyComments.php' => ['craft/comment-placement' => 3],
    'violations/src/OneLineBranches.php' => [
        'craft/no-single-line-control-structure' => 3,
        'block-statement' => 1
    ],
    'violations/src/TernarySoup.php' => ['no-nested-ternary' => 1],
    'violations/src/UsesAliasedFacadeLog.php' => ['craft/no-facade-log' => 1],
    'violations/src/UsesFacadeLog.php' => ['craft/no-facade-log' => 2],
    'violations/src/Model/MutableTrace.php' => ['craft/readonly-value-objects' => 1]
];

/**
 * @param list<non-empty-string> $command
 *
 * @return array{exit_code: int, output: string}
 */
function run(array $command, string $workingDirectory): array
{
    if ($workingDirectory === '') {
        throw new RuntimeException('The working directory cannot be empty.');
    }

    $descriptors = [1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
    /** @var array<int, resource> $pipes */
    $pipes = [];
    $process = proc_open($command, $descriptors, $pipes, $workingDirectory);

    if ($process === false) {
        throw new RuntimeException('Unable to start: ' . implode(', ', $command));
    }

    $stdout = $pipes[1] ?? null;
    $stderr = $pipes[2] ?? null;

    if ($stdout === null || $stderr === null) {
        throw new RuntimeException('Mago was started without its output pipes.');
    }

    $output = (string) stream_get_contents($stdout);
    stream_get_contents($stderr);
    fclose($stdout);
    fclose($stderr);

    return ['exit_code' => proc_close($process), 'output' => $output];
}

/**
 * Extracts `[fixture path, rule code]` pairs from mago's JSON report.
 *
 * Each step narrows one level of the decoded JSON with `is_*` checks; the
 * intermediate `mixed` values are exactly what that requires.
 *
 * @mago-expect analysis:mixed-assignment
 *
 * @return list<array{path: non-empty-string, code: non-empty-string}>
 */
function read_findings(string $output, int $exitCode): array
{
    $report = json_decode($output, associative: true);

    if (!is_array($report) || !is_array($report['issues'] ?? null)) {
        throw new RuntimeException("Unexpected mago output (exit {$exitCode}): " . $output);
    }

    $findings = [];
    foreach ($report['issues'] as $issue) {
        $finding = finding_from($issue);
        if ($finding !== null) {
            $findings[] = $finding;
        }
    }

    return $findings;
}

/**
 * @mago-expect analysis:mixed-assignment
 *
 * @return null|array{path: non-empty-string, code: non-empty-string}
 */
function finding_from(mixed $issue): ?array
{
    if (!is_array($issue)) {
        return null;
    }

    $code = $issue['code'] ?? null;
    $path = fixture_path_from($issue);

    if (!is_string($code) || $code === '' || $path === null) {
        return null;
    }

    return ['path' => $path, 'code' => $code];
}

/**
 * @param array<array-key, mixed> $issue
 *
 * @return null|non-empty-string the fixture path, relative to `dev/mago/fixtures/`
 *
 * @mago-expect analysis:mixed-assignment
 */
function fixture_path_from(array $issue): ?string
{
    $annotations = $issue['annotations'] ?? null;
    $annotation = is_array($annotations) ? $annotations[0] ?? null : null;
    $span = is_array($annotation) ? $annotation['span'] ?? null : null;
    $file = is_array($span) ? $span['file_id'] ?? null : null;
    $name = is_array($file) ? $file['name'] ?? null : null;

    if (!is_string($name) || $name === '') {
        return null;
    }

    $marker = strpos(haystack: $name, needle: 'fixtures/');
    $path = $marker === false ? $name : substr($name, $marker + 9);

    return $path === '' ? null : $path;
}

/**
 * @param array<string, array<string, int>> $findings
 *
 * @return array<string, array<string, int>> sorted by fixture, then by rule
 */
function sorted(array $findings): array
{
    ksort($findings);

    foreach ($findings as $path => $codes) {
        ksort($codes);
        $findings[$path] = $codes;
    }

    return $findings;
}

/**
 * @param array<string, array<string, int>> $findings
 */
function describe(array $findings): string
{
    if ($findings === []) {
        return '  (no findings)';
    }

    $lines = [];
    foreach ($findings as $path => $codes) {
        ksort($codes);
        foreach ($codes as $code => $count) {
            $lines[] = "  {$path}: {$code} x{$count}";
        }
    }

    return implode("\n", $lines);
}

$projectRoot = dirname(__DIR__, levels: 2);
$result = run([
    PHP_BINARY,
    $projectRoot . '/vendor/carthage-software/mago/composer/bin/mago',
    '--config',
    'dev/mago/fixtures/mago.toml',
    'lint',
    '--only',
    'block-statement,craft/comment-placement,craft/forbidden-imports,craft/no-facade-log,'
        . 'craft/no-single-line-control-structure,craft/readonly-value-objects,no-nested-ternary',
    '--reporting-format',
    'json'
], $projectRoot);

$violations = [];
$clean = [];
foreach (read_findings($result['output'], $result['exit_code']) as $finding) {
    $path = $finding['path'];
    $code = $finding['code'];

    if (str_starts_with($path, 'violations/')) {
        $violations[$path][$code] = ( $violations[$path][$code] ?? 0 ) + 1;

        continue;
    }

    $clean[$path][$code] = ( $clean[$path][$code] ?? 0 ) + 1;
}

ksort($violations);
$expected = EXPECTED;
ksort($expected);

$violations = sorted($violations);
$expected = sorted($expected);
$clean = sorted($clean);

if ($violations !== $expected || $clean !== []) {
    fwrite(STDERR, "Custom linter rule fixtures drifted.\n\nExpected:\n" . describe($expected) . "\n\n");
    fwrite(STDERR, "Actual (violations):\n" . describe($violations) . "\n\n");
    fwrite(STDERR, "Actual (clean):\n" . describe($clean) . "\n");

    exit(1);
}

echo
    'Custom linter rules behave as documented: '
        . count($expected)
        . ' fixtures, '
        . array_sum(array_map(array_sum(...), $expected))
        . " findings; clean fixtures are clean.\n";

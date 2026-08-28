<?php

namespace Tests\Unit;

use App\Concerns\RetriesUniqueConstraint;
use Illuminate\Database\QueryException;
use PHPUnit\Framework\TestCase;
use PDOException;

class RetriesUniqueConstraintTest extends TestCase
{
    public function test_retries_on_unique_violation_until_success(): void
    {
        $runner = new class {
            use RetriesUniqueConstraint;

            public int $attempts = 0;

            public function run(): mixed
            {
                return $this->retryOnUniqueViolation(function () {
                    $this->attempts++;

                    if ($this->attempts < 3) {
                        throw $this->queryException(1062, 'Duplicate entry');
                    }

                    return 'ok';
                });
            }

            private function queryException(int $code, string $message): QueryException
            {
                $pdo = new PDOException($message, $code);
                $pdo->errorInfo = ['23000', $code, $message];

                return new QueryException('mysql', 'update `assets` set ...', [], $pdo);
            }
        };

        $this->assertSame('ok', $runner->run());
        $this->assertSame(3, $runner->attempts);
    }

    public function test_rethrows_non_unique_exception(): void
    {
        $runner = new class {
            use RetriesUniqueConstraint;

            public function run(): mixed
            {
                return $this->retryOnUniqueViolation(function () {
                    $pdo = new PDOException('Table not found', 1146);
                    $pdo->errorInfo = ['42000', 1146, 'Table not found'];

                    throw new QueryException('mysql', 'select ...', [], $pdo);
                });
            }
        };

        $this->expectException(QueryException::class);
        $runner->run();
    }

    public function test_gives_up_after_max_attempts_on_persistent_conflict(): void
    {
        $runner = new class {
            use RetriesUniqueConstraint;

            public int $attempts = 0;

            public function run(): mixed
            {
                return $this->retryOnUniqueViolation(function () {
                    $this->attempts++;
                    $pdo = new PDOException('Duplicate entry', 1062);
                    $pdo->errorInfo = ['23000', 1062, 'Duplicate entry'];

                    throw new QueryException('mysql', 'update `assets` set ...', [], $pdo);
                }, 3);
            }
        };

        $this->expectException(QueryException::class);
        $runner->run();
        $this->assertSame(3, $runner->attempts);
    }
}

<?php

/*
 * This file is part of the Fidry\AliceDataFixtures package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Fidry\AliceDataFixtures\Bridge\Propel2\Purger;

use Fidry\AliceDataFixtures\Persistence\PurgeMode;
use Fidry\AliceDataFixtures\Persistence\PurgerFactoryInterface;
use Fidry\AliceDataFixtures\Persistence\PurgerInterface;
use Illuminate\Database\Migrations\MigrationRepositoryInterface;
use Illuminate\Database\Migrations\Migrator;
use InvalidArgumentException;
use Nelmio\Alice\IsAServiceTrait;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Propel;
use RuntimeException;

/**
 * @author Daniel Leech <daniel@dantleech.com>
 *
 * @final
 */
/*final*/ class ModelPurger implements PurgerInterface, PurgerFactoryInterface
{
    use IsAServiceTrait;

    /**
     * @var ConnectionInterface
     */
    private $connection;

    /**
     * @var string
     */
    private $generatedSqlPath;

    public function __construct(ConnectionInterface $connection, string $generatedSqlPath)
    {
        $this->connection = $connection;
        $this->generatedSqlPath = $generatedSqlPath;
    }

    /**
     * @inheritdoc
     */
    public function create(PurgeMode $mode, PurgerInterface $purger = null): PurgerInterface
    {
        if ($mode == PurgeMode::createDeleteMode()) {
            throw new InvalidArgumentException(
                sprintf(
                    'Cannot purge database in delete mode with "%s" (not supported).',
                    __CLASS__
                )
            );
        }

        return new self();
    }

    /**
     * @inheritdoc
     */
    public function purge()
    {
        $sqlPath = sprintf('%s/%s.sql', $this->generatedSqlPath, $this->connection->getName());

        if (false === file_exists($sqlPath)) {
            throw new RuntimeException(
                sprintf(
                    'No propel generated SQL file exists at "%s", do you need to generate it?',
                    $sqlPath
                )
            );
        }

        $this->connection->exec(file_get_contents($sqlPath));
    }
}

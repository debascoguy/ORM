<?php

namespace Emma\ORM\Connection;

use Emma\Common\Singleton\Singleton;
use Emma\Dbal\Connection\ConnectionProperty;
use Emma\Dbal\Connection\PDOConnection;
use Emma\Dbal\ConnectionManager;

class Connection
{
    use Singleton;

    protected PDOConnection $activeConnection;

    /**
     * @param ConnectionProperty|array $connectionDetails
     * @return void
     */
    public function connect(ConnectionProperty|array $connectionDetails): void
    {
        if ($connectionDetails instanceof ConnectionProperty) {
            $this->setActiveConnection(ConnectionManager::getConnection($connectionDetails));
        } else {
            $this->setActiveConnection(ConnectionManager::createConnection($connectionDetails));
        }
    }

    /**
     * @return PDOConnection
     */
    public function getActiveConnection(): PDOConnection
    {
        return $this->activeConnection;
    }

    /**
     * @param PDOConnection $activeConnection
     * @return Connection
     */
    public function setActiveConnection(PDOConnection $activeConnection): Connection
    {
        $this->activeConnection = $activeConnection;
        return $this;
    }
}
<?php
/**
 * Created by ambulance@eterosf.ru
 * Date: 25.05.18
 * Time: 19:24
 */

namespace Etersoft\Typos;

use JsonRPC\Server;

/**
 * Class TyposClient
 *
 * Allows to manage typos-fixing request from typo-fixing server.
 * Part of the Etersoft Typo system.
 * Uses a json-rpc 2.0 protocol.
 *
 * @package Etersoft\Typos
 */
class TyposClient
{
    /**
     * @var Server
     */
    private $server;

    private $interface;

    /**
     * TyposAbstractClient constructor.
     *
     * @param TyposClientInterface $interface Interface of server
     */
    public function __construct(TyposClientInterface $interface)
    {
        $this->interface = $interface;

        $this->server = new Server();
        $procedureHandler = $this->server->getProcedureHandler();

        $procedureHandler->withObject($this->interface);
    }

    /**
     * Execute a json-rpc server
     * @return string
     */
    public function run() {
        return $this->server->execute();
    }
}

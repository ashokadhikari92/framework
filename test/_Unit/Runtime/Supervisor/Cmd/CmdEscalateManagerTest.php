<?php

namespace Kraken\_Unit\Runtime\Supervisor;

use Kraken\_Unit\Runtime\_T\TSolver;
use Kraken\Channel\ChannelBaseInterface;
use Kraken\Channel\ChannelProtocol;
use Kraken\Channel\Extra\Request;
use Kraken\Runtime\RuntimeCommand;
use Kraken\Runtime\Supervisor\Cmd\CmdEscalateManager;
use Exception;
use stdClass;

class CmdEscalateManagerTest extends TSolver
{
    /**
     * @var string
     */
    protected $class = CmdEscalateManager::class;

    /**
     *
     */
    public function testApiHandler_InvokesProperAction()
    {
        $ex = new Exception();
        $params = [];
        $result = new StdClass;

        $call = $this->getMock(Request::class, [], [], '', false);
        $call
            ->expects($this->once())
            ->method('call')
            ->will($this->returnValue($result));

        $solver  = $this->createSolver([], [ 'createRequest' ]);
        $solver
            ->expects($this->once())
            ->method('createRequest')
            ->with(
                $this->isInstanceOf(ChannelBaseInterface::class),
                'parent',
                $this->isInstanceOf(RuntimeCommand::class)
            )
            ->will($this->returnValue($call));

        $this->createChannel();

        $this->assertSame(
            $result,
            $this->callProtectedMethod(
                $solver, 'handler', [ $ex, $params ]
            )
        );
    }

    /**
     *
     */
    public function testProtectedApiCreateRequest_CreatesRequest()
    {
        $channel  = $this->getMock(ChannelBaseInterface::class, [], [], '', false);
        $channel
            ->expects($this->any())
            ->method('createProtocol')
            ->will($this->returnCallback(function($message) {
                return new ChannelProtocol('', '', '', '', $message);
            }));
        $receiver = 'receiver';
        $command  = 'command';

        $cmd = $this->createSolver();

        $req = $this->callProtectedMethod($cmd, 'createRequest', [ $channel, $receiver, $command ]);

        $this->assertInstanceOf(Request::class, $req);
        $this->assertSame($channel,  $this->getProtectedProperty($req, 'channel'));
        $this->assertSame($receiver, $this->getProtectedProperty($req, 'name'));
        $this->assertSame($command,  $this->getProtectedProperty($req, 'message')->getMessage());
    }
}
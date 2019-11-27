<?php

/*
 * Raver - Unofficial Rave PHP SDK
 *
 * Copyright - 2019
 *
 * MIT
 *
 * Kofi Cypher <skcypher6@gmail.com>
 */

namespace Raver\Tests;

use PHPUnit\Framework\TestCase;

use Raver\Executor;

class RaverTest extends TestCase
{
    public function testTruthy()
    {
        $this->assertTrue(true);
    }

    public function testExecutorInstance()
    {
      $executor  = new Executor();

      $this->assertInstanceOf(Executor::class, $executor);
    }
}

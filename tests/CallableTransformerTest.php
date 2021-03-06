<?php

namespace Remodel\Tests;

use PHPUnit\Framework\TestCase;
use Remodel\CallableTransformer;
use Remodel\Subjects\Item;

/**
 * @covers Remodel\CallableTransformer
 * @covers Remodel\Subjects\Item
 * @covers Remodel\Transformer
 * @covers Remodel\Subjects\Subject
 */
class CallableTransformerTest extends TestCase
{
    protected function getData()
    {
        return [
            "id" => 1,
            "email" => "john@example.org",
            "name" => "John Doe",
            "password" => '$2y$10$EfIqopgVNY8Bdw/GiIAOl.PVMyERHG5zfE0fYh9FtWvmECS1ZWIdu',
        ];
    }

    public function transform(array $data)
    {
        return [
            "id" => $data['id'],
            "email" => $data['email'],
            "name" => $data['name'],
        ];
    }

    public function test_closure_is_supported()
    {
        $dataSource = $this->getData();

        $item = new Item(
            $this->getData(),
            new CallableTransformer(function(array $data): array {

                return [
                    "id" => $data['id'],
                    "email" => $data['email'],
                    "name" => $data['name'],
                ];

            })
        );

        $dataTransformed = $item->remodel();

        $this->assertTrue(\is_array($item->remodel()));
        $this->assertEquals($dataSource['id'], $dataTransformed['id']);
        $this->assertEquals($dataSource['email'], $dataTransformed['email']);
        $this->assertEquals($dataSource['name'], $dataTransformed['name']);
        $this->assertFalse(isset($dataTransformed['password']));
    }

    public function test_callable_is_supported()
    {
        $dataSource = $this->getData();

        $item = new Item(
            $this->getData(),
            new CallableTransformer([$this, 'transform'])
        );

        $dataTransformed = $item->remodel();

        $this->assertTrue(\is_array($item->remodel()));
        $this->assertEquals($dataSource['id'], $dataTransformed['id']);
        $this->assertEquals($dataSource['email'], $dataTransformed['email']);
        $this->assertEquals($dataSource['name'], $dataTransformed['name']);
        $this->assertFalse(isset($dataTransformed['password']));
    }
}
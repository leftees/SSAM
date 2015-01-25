<?php
class ExampleStringTest extends PHPUnit_Framework_TestCase
{
    // ...

    public function testExampleString()
    {
        $example = "hello world";

        // Assert
        $this->assertEquals("hello world", $example);
    }

    // ...
}

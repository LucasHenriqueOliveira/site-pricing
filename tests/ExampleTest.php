<?php

use Laravel\Lumen\Testing\DatabaseTransactions;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testExample()
    {
        $this->get('/');
        $response = $this->response;
        $content = json_decode($response->content());
        $this->assertEquals($response->status(), 401);
        $this->assertEquals($content->message, "Failed to authenticate because of bad credentials or an invalid authorization header.");
    }
}

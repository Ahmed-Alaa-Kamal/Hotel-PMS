<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../Controllers/AuthController.php';
require_once __DIR__ . '/../Models/User.php';

class LoginTest extends TestCase
{
    public function testValidLogin()
    {
        $auth = new AuthController();

        $user = new User();
        $user->username = "manager";
        $user->password = "123456";

        $result = $auth->login($user);

        $this->assertTrue($result);
    }

    public function testInvalidUser()
    {
        $auth = new AuthController();

        $user = new User();
        $user->username = "youssef";
        $user->password = "123456";

        $result = $auth->login($user);

        $this->assertFalse($result);
    }

    public function testInvalidPassword()
    {
        $auth = new AuthController();

        $user = new User();
        $user->username = "admin";
        $user->password = "wrongpassword";

        $result = $auth->login($user);

        $this->assertFalse($result);
    }

    public function testEmptyFields()
    {
        $auth = new AuthController();

        $user = new User();
        $user->username = "";
        $user->password = "";

        $result = $auth->login($user);

        $this->assertFalse($result);
    }
    public function testUserEmpty()
    {
        $auth = new AuthController();

        $user = new User();
        $user->username = "";
        $user->password = "123456";

        $result = $auth->login($user);

        $this->assertFalse($result);
    }
    public function testPassEmpty()
    {
        $auth = new AuthController();

        $user = new User();
        $user->username = "manager";
        $user->password = "";

        $result = $auth->login($user);

        $this->assertFalse($result);
    }
    public function testSecurity()
    {
        $auth = new AuthController();

        $user = new User();
        $user->username = "<script>alert('hack')</script>";
        $user->password = "123456'";

        $result = $auth->login($user);

        $this->assertFalse($result);
    }
}
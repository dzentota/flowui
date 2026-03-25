<?php

namespace FlowUI\Tests;

use PHPUnit\Framework\TestCase;
use FlowUI\Validation\Validator;
use FlowUI\Core\Config;

class ValidatorTest extends TestCase
{
    private Validator $validator;

    protected function setUp(): void
    {
        $this->validator = new Validator(new Config());
    }

    public function testRequiredRule()
    {
        $errors = $this->validator->validate(
            ['name' => ''],
            ['name' => 'required']
        );

        $this->assertNotEmpty($errors);
        $this->assertArrayHasKey('name', $errors);
    }

    public function testRequiredRulePasses()
    {
        $errors = $this->validator->validate(
            ['name' => 'John'],
            ['name' => 'required']
        );

        $this->assertEmpty($errors);
    }

    public function testEmailRule()
    {
        $errors = $this->validator->validate(
            ['email' => 'invalid-email'],
            ['email' => 'email']
        );

        $this->assertNotEmpty($errors);
        $this->assertArrayHasKey('email', $errors);
    }

    public function testEmailRulePasses()
    {
        $errors = $this->validator->validate(
            ['email' => 'test@example.com'],
            ['email' => 'email']
        );

        $this->assertEmpty($errors);
    }

    public function testMinRule()
    {
        $errors = $this->validator->validate(
            ['password' => 'short'],
            ['password' => 'min:8']
        );

        $this->assertNotEmpty($errors);
        $this->assertArrayHasKey('password', $errors);
    }

    public function testMinRulePasses()
    {
        $errors = $this->validator->validate(
            ['password' => 'longenough'],
            ['password' => 'min:8']
        );

        $this->assertEmpty($errors);
    }

    public function testMultipleRules()
    {
        $errors = $this->validator->validate(
            ['email' => 'short'],
            ['email' => 'required|email|min:10']
        );

        $this->assertNotEmpty($errors);
        $this->assertArrayHasKey('email', $errors);
        $this->assertIsArray($errors['email']);
    }

    public function testNumericRule()
    {
        $errors = $this->validator->validate(
            ['age' => 'not-a-number'],
            ['age' => 'numeric']
        );

        $this->assertNotEmpty($errors);
    }

    public function testNumericRulePasses()
    {
        $errors = $this->validator->validate(
            ['age' => '25'],
            ['age' => 'numeric']
        );

        $this->assertEmpty($errors);
    }
}

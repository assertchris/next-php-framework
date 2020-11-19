<?php

namespace Next;

use Next\Concerns\CannotBeCreated;
use Next\Http\Request;
use Rakit\Validation\Validator as RakitValidator;

class Validator
{
    use CannotBeCreated;

    private RakitValidator $validator;

    private function __construct()
    {
        $this->validator = new RakitValidator();
    }

    public static function __callStatic(string $method, $params)
    {
        return static::instance()->$method(...$params);
    }

    private function run(Request $request, array $rules = [])
    {
        $validation = $this->validator->make($request->only(array_keys($rules)), $rules);
        $validation->validate();

        return $validation;
    }
}

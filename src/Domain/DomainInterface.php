<?php

namespace App\Domain;
use Symfony\Component\HttpFoundation\Request;


interface DomainInterface {

    public function getAll(Request $request): array;
    public function post(Request $request);
}
<?php

namespace App\BeBundle\Domain\Interfaces;
use App\BeBundle\Domain\Model\DomainResponse;
use Symfony\Component\HttpFoundation\Request;


interface DomainInterface {

    public function getAll(Request $request): DomainResponse;
    public function post(Request $request);
}
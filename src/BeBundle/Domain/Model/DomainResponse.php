<?php

namespace App\BeBundle\Domain\Model;

class DomainResponse {

    private int $statusCode;
    private     $content;

    public function getStatusCode(): int {
        return $this->statusCode;
    }

    public function setStatusCode(int $statusCode): DomainResponse {
        $this->statusCode = $statusCode;

        return $this;
    }

    public function getContent() {
        return $this->content;
    }

    public function setContent($content): DomainResponse {
        $this->content = $content;

        return $this;
    }
}
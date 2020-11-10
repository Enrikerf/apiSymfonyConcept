<?php

namespace App\Data\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;


/**
 * @ORM\Entity(repositoryClass="App\Repository\PruebaRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Prueba  {

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;
    /**
     * @Groups({"public"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $firstName = null;


    public function __construct() {
    }

    /**
     * @return mixed
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @param mixed $id
     *
     * @return Prueba
     */
    public function setId($id) {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getFirstName(): ?string {
        return $this->firstName;
    }

    /**
     * @param string|null $firstName
     *
     * @return Prueba
     */
    public function setFirstName(?string $firstName): Prueba {
        $this->firstName = $firstName;

        return $this;
    }


}

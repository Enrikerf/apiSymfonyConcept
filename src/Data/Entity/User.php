<?php

namespace App\Data\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;


/**
 * @ORM\Entity(repositoryClass="App\Data\Repository\UserRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class User  {

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @Groups({"public"})a
     * @ORM\Column(type="integer")
     */
    private $id;
    /**
     * @Groups({"public"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $firstName = null;
    /**
     * @Groups({"public"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $lastName = null;
    /**
     * @Groups({"public"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $fullName = null;
    /**
     * @Groups({"public"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $username = null;
    /**
     * @Groups({"public"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $email = null;
    /**
     * @Groups({"public"})
     * @ORM\Column(name="roles", type="simple_array", nullable=true)
     */
    private array $roles = [];
    /**
     * @Groups({"public"})
     * @ORM\Column(name="created_at", type="datetime")
     */
    private ?DateTime $createdAt = null;
    /**
     * @Groups({"public"})
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     */
    private ?DateTime $updatedAt = null;
    /**
     * @Groups({"public"})
     * @ORM\column(name="soft_delete", type="boolean", nullable=false)
     */
    private bool $softDelete = false;

    public function __construct() {
        $this->roles = [];
        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();
    }

    /**
     * Gets triggered only on insert
     *
     * @ORM\PrePersist
     */
    public function onPrePersist(): void {
        $this->createdAt = new DateTime('now');
    }

    /**
     * Gets triggered every time on update
     *
     * @ORM\PreUpdate
     */
    public function onPreUpdate(): void {
        $this->updatedAt = new DateTime('now');
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
     * @return User
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
     * @return User
     */
    public function setFirstName(?string $firstName): User {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getLastName(): ?string {
        return $this->lastName;
    }

    /**
     * @param string|null $lastName
     *
     * @return User
     */
    public function setLastName(?string $lastName): User {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getFullName(): ?string {
        return $this->fullName;
    }

    /**
     * @param string|null $fullName
     *
     * @return User
     */
    public function setFullName(?string $fullName): User {
        $this->fullName = $fullName;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getUsername(): ?string {
        return $this->username;
    }

    /**
     * @param string|null $username
     *
     * @return User
     */
    public function setUsername(?string $username): User {
        $this->username = $username;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string {
        return $this->email;
    }

    /**
     * @param string|null $email
     *
     * @return User
     */
    public function setEmail(?string $email): User {
        $this->email = $email;

        return $this;
    }

    /**
     * @return array
     */
    public function getRoles(): array {
        return $this->roles;
    }

    /**
     * @param array $roles
     *
     * @return User
     */
    public function setRoles(array $roles): User {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getCreatedAt(): ?DateTime {
        return $this->createdAt;
    }

    /**
     * @param DateTime|null $createdAt
     *
     * @return User
     */
    public function setCreatedAt(?DateTime $createdAt): User {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getUpdatedAt(): ?DateTime {
        return $this->updatedAt;
    }

    /**
     * @param DateTime|null $updatedAt
     *
     * @return User
     */
    public function setUpdatedAt(?DateTime $updatedAt): User {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return bool
     */
    public function isSoftDelete(): bool {
        return $this->softDelete;
    }

    /**
     * @param bool $softDelete
     *
     * @return User
     */
    public function setSoftDelete(bool $softDelete): User {
        $this->softDelete = $softDelete;

        return $this;
    }


}

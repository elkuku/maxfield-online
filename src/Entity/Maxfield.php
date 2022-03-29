<?php

namespace App\Entity;

use App\Repository\MaxfieldRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;

#[Entity(repositoryClass: MaxfieldRepository::class)]
class Maxfield
{
    #[Id]
    #[GeneratedValue]
    #[Column(type: Types::INTEGER)]
    private ?int $id = 0;

    #[Column(type: Types::STRING, length: 255)]
    private ?string $name;

    /**
     * @var array<string, array<Waypoint|\stdClass>>
     */
    #[Column(type: Types::JSON)]
    private ?array $jsonData = [];

    #[ManyToOne(targetEntity: User::class, inversedBy: 'maxfields')]
    #[JoinColumn(nullable: false)]
    private ?User $owner;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return array<string, array<Waypoint|\stdClass>>|null
     */
    public function getJsonData(): ?array
    {
        return $this->jsonData;
    }

    /**
     * @param array<string, array<Waypoint|\stdClass>>|null $jsonData
     */
    public function setJsonData(array $jsonData): self
    {
        $this->jsonData = $jsonData;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): self
    {
        $this->owner = $owner;

        return $this;
    }
}

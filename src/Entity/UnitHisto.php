<?php

namespace App\Entity;

use App\Repository\UnitHistoRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UnitHistoRepository::class)]
class UnitHisto
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'unitHistos')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Unit $unit = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?State $state = null;

    #[ORM\Column]
    private ?\DateTime $updateDate = null;

    #[ORM\Column(length: 255)]
    private ?string $changes = null;

    #[ORM\Column(length: 255)]
    private ?string $unitDescription = null;

    #[ORM\ManyToOne]
    private ?Rental $rental = null;

    public function getId(): ?int
    {
        return $this->id;
    }
    public function getUnit(): ?unit
    {
        return $this->unit;
    }

    public function setUnit(?unit $unit): static
    {
        $this->unit = $unit;

        return $this;
    }

    public function getState(): ?state
    {
        return $this->state;
    }

    public function setState(?state $state): static
    {
        $this->state = $state;

        return $this;
    }

    public function getUpdateDate(): ?\DateTime
    {
        return $this->updateDate;
    }

    public function setUpdateDate(\DateTime $updateDate): static
    {
        $this->updateDate = $updateDate;

        return $this;
    }

    public function getChanges(): ?string
    {
        return $this->changes;
    }

    public function setChanges(string $changes): static
    {
        $this->changes = $changes;

        return $this;
    }

    public function getUnitDescription(): ?string
    {
        return $this->unitDescription;
    }

    public function setUnitDescription(string $unitDescription): static
    {
        $this->unitDescription = $unitDescription;

        return $this;
    }

    public function getRental(): ?Rental
    {
        return $this->rental;
    }

    public function setRental(?Rental $rental): static
    {
        $this->rental = $rental;

        return $this;
    }
}

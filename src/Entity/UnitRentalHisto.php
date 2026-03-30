<?php

namespace App\Entity;

use App\Repository\UnitRentalHistoRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UnitRentalHistoRepository::class)]
class UnitRentalHisto
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $startDate = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $endDate = null;

    #[ORM\ManyToOne(inversedBy: 'unitRentalHistos')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Unit $unit = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Rental $rental = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStartDate(): ?\DateTime
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTime $startDate): static
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeInterface $endDate): static
    {
        $this->endDate = $endDate;

        return $this;
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

    public function getRental(): ?rental
    {
        return $this->rental;
    }

    public function setRental(rental $rental): static
    {
        $this->rental = $rental;

        return $this;
    }
}

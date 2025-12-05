<?php

namespace App\Entity;

use App\Repository\UnitRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UnitRepository::class)]
class Unit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $label = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $size = null;

    #[ORM\ManyToOne(inversedBy: 'units')]
    #[ORM\JoinColumn(nullable: false)]
    private ?bay $bay = null;

    /**
     * @var Collection<int, Intervention>
     */
    #[ORM\OneToMany(targetEntity: Intervention::class, mappedBy: 'unit')]
    private Collection $interventions;

    #[ORM\ManyToOne(inversedBy: 'units')]
    private ?rental $rental = null;

    /**
     * @var Collection<int, UnitRentalHisto>
     */
    #[ORM\OneToMany(targetEntity: UnitRentalHisto::class, mappedBy: 'unit')]
    private Collection $unitRentalHistos;

    /**
     * @var Collection<int, UnitHisto>
     */
    #[ORM\OneToMany(targetEntity: UnitHisto::class, mappedBy: 'unit')]
    private Collection $unitHistos;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?state $state = null;

    public function __construct()
    {
        $this->interventions = new ArrayCollection();
        $this->unitRentalHistos = new ArrayCollection();
        $this->unitHistos = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function getSize(): ?string
    {
        return $this->size;
    }

    public function setSize(string $size): static
    {
        $this->size = $size;

        return $this;
    }

    public function getBay(): ?bay
    {
        return $this->bay;
    }

    public function setBay(?bay $bay): static
    {
        $this->bay = $bay;

        return $this;
    }

    /**
     * @return Collection<int, Intervention>
     */
    public function getInterventions(): Collection
    {
        return $this->interventions;
    }

    public function addIntervention(Intervention $intervention): static
    {
        if (!$this->interventions->contains($intervention)) {
            $this->interventions->add($intervention);
            $intervention->setUnit($this);
        }

        return $this;
    }

    public function removeIntervention(Intervention $intervention): static
    {
        if ($this->interventions->removeElement($intervention)) {
            // set the owning side to null (unless already changed)
            if ($intervention->getUnit() === $this) {
                $intervention->setUnit(null);
            }
        }

        return $this;
    }

    public function getRental(): ?rental
    {
        return $this->rental;
    }

    public function setRental(?rental $rental): static
    {
        $this->rental = $rental;

        return $this;
    }

    /**
     * @return Collection<int, UnitRentalHisto>
     */
    public function getUnitRentalHistos(): Collection
    {
        return $this->unitRentalHistos;
    }

    public function addUnitRentalHisto(UnitRentalHisto $unitRentalHisto): static
    {
        if (!$this->unitRentalHistos->contains($unitRentalHisto)) {
            $this->unitRentalHistos->add($unitRentalHisto);
            $unitRentalHisto->setUnit($this);
        }

        return $this;
    }

    public function removeUnitRentalHisto(UnitRentalHisto $unitRentalHisto): static
    {
        if ($this->unitRentalHistos->removeElement($unitRentalHisto)) {
            // set the owning side to null (unless already changed)
            if ($unitRentalHisto->getUnit() === $this) {
                $unitRentalHisto->setUnit(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, UnitHisto>
     */
    public function getUnitHistos(): Collection
    {
        return $this->unitHistos;
    }

    public function addUnitHisto(UnitHisto $unitHisto): static
    {
        if (!$this->unitHistos->contains($unitHisto)) {
            $this->unitHistos->add($unitHisto);
            $unitHisto->setUnit($this);
        }

        return $this;
    }

    public function removeUnitHisto(UnitHisto $unitHisto): static
    {
        if ($this->unitHistos->removeElement($unitHisto)) {
            // set the owning side to null (unless already changed)
            if ($unitHisto->getUnit() === $this) {
                $unitHisto->setUnit(null);
            }
        }

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
}

<?php

namespace App\Entity;

use App\Repository\BayRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BayRepository::class)]
class Bay
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $label = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $size = null;

    /**
     * @var Collection<int, Unit>
     */
    #[ORM\OneToMany(targetEntity: Unit::class, mappedBy: 'bay', orphanRemoval: true)]
    private Collection $units;

    /**
     * @var Collection<int, Intervention>
     */
    #[ORM\OneToMany(targetEntity: Intervention::class, mappedBy: 'bay')]
    private Collection $interventions;

    /**
     * @var Collection<int, BayHisto>
     */
    #[ORM\OneToMany(targetEntity: BayHisto::class, mappedBy: 'bay')]
    private Collection $bayHistos;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?state $state = null;

    public function __construct()
    {
        $this->units = new ArrayCollection();
        $this->interventions = new ArrayCollection();
        $this->bayHistos = new ArrayCollection();
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

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function setSize(int $size): static
    {
        $this->size = $size;

        return $this;
    }

    /**
     * @return Collection<int, Unit>
     */
    public function getUnits(): Collection
    {
        return $this->units;
    }

    public function addUnit(Unit $unit): static
    {
        if (!$this->units->contains($unit)) {
            $this->units->add($unit);
            $unit->setBay($this);
        }

        return $this;
    }

    public function removeUnit(Unit $unit): static
    {
        if ($this->units->removeElement($unit)) {
            // set the owning side to null (unless already changed)
            if ($unit->getBay() === $this) {
                $unit->setBay(null);
            }
        }

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
            $intervention->setBay($this);
        }

        return $this;
    }

    public function removeIntervention(Intervention $intervention): static
    {
        if ($this->interventions->removeElement($intervention)) {
            // set the owning side to null (unless already changed)
            if ($intervention->getBay() === $this) {
                $intervention->setBay(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, BayHisto>
     */
    public function getBayHistos(): Collection
    {
        return $this->bayHistos;
    }

    public function addBayHisto(BayHisto $bayHisto): static
    {
        if (!$this->bayHistos->contains($bayHisto)) {
            $this->bayHistos->add($bayHisto);
            $bayHisto->setBay($this);
        }

        return $this;
    }

    public function removeBayHisto(BayHisto $bayHisto): static
    {
        if ($this->bayHistos->removeElement($bayHisto)) {
            // set the owning side to null (unless already changed)
            if ($bayHisto->getBay() === $this) {
                $bayHisto->setBay(null);
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

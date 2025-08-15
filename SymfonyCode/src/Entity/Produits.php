<?php

namespace App\Entity;

use App\Repository\ProduitsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProduitsRepository::class)]
class Produits
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nomProduit = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $prixProduit = null;

    #[ORM\Column(length: 255)]
    private ?string $imageProduitAvant = null;

    #[ORM\Column(length: 255)]
    private ?string $imageProduitApres = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomProduit(): ?string
    {
        return $this->nomProduit;
    }

    public function setNomProduit(string $nomProduit): static
    {
        $this->nomProduit = $nomProduit;

        return $this;
    }

    public function getPrixProduit(): ?string
    {
        return $this->prixProduit;
    }

    public function setPrixProduit(string $prixProduit): static
    {
        $this->prixProduit = $prixProduit;

        return $this;
    }

    public function getImageProduitAvant(): ?string
    {
        return $this->imageProduitAvant;
    }

    public function setImageProduitAvant(string $imageProduitAvant): static
    {
        $this->imageProduitAvant = $imageProduitAvant;

        return $this;
    }

    public function getImageProduitApres(): ?string
    {
        return $this->imageProduitApres;
    }

    public function setImageProduitApres(string $imageProduitApres): static
    {
        $this->imageProduitApres = $imageProduitApres;

        return $this;
    }
}

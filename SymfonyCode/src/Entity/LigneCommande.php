<?php
namespace App\Entity;

use App\Repository\LigneCommandeRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;

#[ORM\Entity(repositoryClass: LigneCommandeRepository::class)]
class LigneCommande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Commande::class, inversedBy: 'lignes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Commande $commande = null;

    #[ORM\Column(length: 255)]
    private string $nomProduit;

    #[ORM\Column(type: 'float')]
    private float $prixProduit;

    #[ORM\Column]
    private int $quantite;

    public function getId(): ?int 
    { 
        return $this->id; 
    }
    public function getCommande(): ?Commande 
    { 
        return $this->commande; 
    }
    public function setCommande(?Commande $commande): static 
    { 
        $this->commande = $commande; 
        return $this; 
    }
    public function getNomProduit(): string 
    {
        return $this->nomProduit;
     }
    public function setNomProduit(string $nomProduit): static 
    {
        $this->nomProduit = $nomProduit; 
        return $this; 
    }
    public function getPrixProduit(): float 
    {
        return $this->prixProduit; 
}
    public function setPrixProduit(float $prixProduit): static 
    { 
        $this->prixProduit = $prixProduit; 
        return $this; 
    }
    public function getQuantite(): int 
    { 
        return $this->quantite; 
    }
    public function setQuantite(int $quantite): static 
    { 
        $this->quantite = $quantite; 
        return $this; 
    }
}

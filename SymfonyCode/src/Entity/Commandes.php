<?php

namespace App\Entity;

use App\Repository\CommandesRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;

#[ORM\Entity(repositoryClass: CommandesRepository::class)]
class Commandes
{

    public const STATUT_EN_ATTENTE = 'EN_ATTENTE';
    public const STATUT_EN_COURS = 'EN_COURS';
    public const STATUT_LIVREE = 'LIVREE';
    public const STATUT_ANNULEE = 'ANNULEE';

    public const STATUTS = [
        self::STATUT_EN_ATTENTE => 'En attente',
        self::STATUT_EN_COURS => 'En cours',
        self::STATUT_LIVREE => 'Livrée',
        self::STATUT_ANNULEE => 'Annulée'
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $dateCommande = null;

    #[ORM\Column(type: 'float')]
    private ?float $prixTotal = null;


    #[ORM\Column(length: 20)]
    private ?string $statut = null;

    #[ORM\ManyToOne(inversedBy: 'commandes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $utilisateur = null;

    public function getId(): ?int
    {
        return $this->id;
    }

   public function getDateCommande(): ?\DateTimeImmutable
    {
        return $this->dateCommande;
    }

    public function setDateCommande(\DateTimeImmutable $dateCommande): static
    {
        $this->dateCommande = $dateCommande;
        return $this;
    }

    public function getPrixTotal(): ?float
    {
        return $this->prixTotal;
    }

    public function setPrixTotal(float $prixTotal): static
    {
        $this->prixTotal = $prixTotal;
        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        if (!array_key_exists($statut, self::STATUTS)) {
            throw new \InvalidArgumentException(sprintf('Statut invalide "%s"', $statut));
        }

        $this->statut = $statut;
        return $this;
    }

    public function getStatutLibelle(): string
    {
        return self::STATUTS[$this->statut] ?? $this->statut;
    }

    public function getUtilisateur(): ?User
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?User $utilisateur): static
    {
        $this->utilisateur = $utilisateur;

        return $this;
    }

    public function __construct()
    {
        $this->dateCommande = new \DateTimeImmutable();
        $this->statut = self::STATUT_EN_ATTENTE;
    }


}

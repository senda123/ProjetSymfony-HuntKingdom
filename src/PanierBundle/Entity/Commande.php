<?php

namespace PanierBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * Commande
 *
 * @ORM\Table(name="commande")
 * @ORM\Entity(repositoryClass="PanierBundle\Repository\CommandeRepository")
 */
class Commande
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="reference", type="string", length=255,unique=true)
     * @Assert\NotBlank()
     */
    private $reference;

    /**
     * @var int
     *
     * @ORM\Column(name="quantite_c", type="integer")
     *
     * @Assert\NotBlank()
     */
    private $quantiteC;

    /**
     * @var int
     *
     * @ORM\Column(name="etat_c", type="integer")
     */
    private $etatC;

    /**
     * @return int
     */
    public function getEtatC()
    {
        return $this->etatC;
    }

    /**
     * @param int $etatC
     */
    public function setEtatC($etatC)
    {
        $this->etatC = $etatC;
    }


    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_c", type="date")
     */
    private $dateC;

    /**
     * @var float
     *
     * @ORM\Column(name="total_c", type="float")
     */
    private $totalC;

    /**
     * @ORM\ManyToOne(targetEntity="ProduitBundle\Entity\Produit")
     * @ORM\JoinColumn(name="idP",referencedColumnName="id")
     */
    private $idP;

    /**
     * @ORM\OneToOne(targetEntity="UserBundle\Entity\User")
     * @ORM\JoinColumn(name="idUser", referencedColumnName="id")
     */

    private $idUser;



    /**
     * @return mixed
     */
    public function getIdUser()
    {
        return $this->idUser;
    }

    /**
     * @param mixed $idUser
     */

    public function setIdUser($idUser)
    {
        $this->idUser = $idUser;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * Set reference
     *
     * @param string $reference
     *
     * @return Commande
     */
    public function setReference($reference)
    {
        $this->reference = $reference;

        return $this;
    }

    /**
     * Get reference
     *
     * @return string
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * Set quantiteC
     *
     * @param integer $quantiteC
     *
     * @return Commande
     */

    public function setQuantiteC($quantiteC)
    {
        $this->quantiteC = $quantiteC;

        return $this;
    }

    /**
     * Get quantiteC
     *
     * @return int
     */

    public function getQuantiteC()
    {
        return $this->quantiteC;
    }

    /**
     * Set dateC
     *
     * @param \DateTime $dateC
     *
     * @return Commande
     */
    public function setDateC($dateC)
    {
        $this->dateC = $dateC;

        return $this;
    }

    /**
     * Get dateC
     *
     * @return \DateTime
     */
    public function getDateC()
    {
        return $this->dateC;
    }

    /**
     * Set totalC
     *
     * @param float $totalC
     *
     * @return Commande
     */
    public function setTotalC($totalC)
    {
        $this->totalC = $totalC;

        return $this;
    }

    /**
     * Get totalC
     *
     * @return float
     */
    public function getTotalC()
    {
        return $this->totalC;
    }

    /**
     * Set idP
     *
     * @param integer $idP
     *
     * @return Commande
     */
    public function setIdP($idP)
    {
        $this->idP = $idP;

        return $this;
    }

    /**
     * Get idP
     *
     * @return int
     */
    public function getIdP()
    {
        return $this->idP;
    }

}


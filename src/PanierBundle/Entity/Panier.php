<?php

namespace PanierBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Panier
 *
 * @ORM\Table(name="panier")
 * @ORM\Entity(repositoryClass="PanierBundle\Repository\PanierRepository")
 */
class Panier
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=true)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="qte_p", type="integer")
     */
    private $qteP;



    /**
     * @var
     * @ORM\ManyToOne(targetEntity="ProduitBundle\Entity\Produit")
     * @ORM\JoinColumn(name="id_p",referencedColumnName="id", onDelete="CASCADE")
     */
    private $id_p;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set qteP
     *
     * @param integer $qteP
     *
     * @return Panier
     */
    public function setQteP($qteP)
    {
        $this->qteP = $qteP;

        return $this;
    }

    /**
     * Get qteP
     *
     * @return int
     */
    public function getQteP()
    {
        return $this->qteP;
    }


    /**
     * @return mixed
     */
    public function getIdP()
    {
        return $this->id_p;
    }

    /**
     * @param mixed $id_p
     */
    public function setIdP($id_p)
    {
        $this->id_p = $id_p;
    }


}


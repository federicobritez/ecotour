<?php

namespace AppBundle\Entity;

/**
 * HorariosReserva
 */
class HorariosReserva
{
    /**
     * @var integer
     */
    private $reservaId;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \AppBundle\Entity\HorariosServicio
     */
    private $horariosServicio;

    /**
     * @var \AppBundle\Entity\Reserva
     */
    private $reserva1;


    /**
     * Set reservaId
     *
     * @param integer $reservaId
     *
     * @return HorariosReserva
     */
    public function setReservaId($reservaId)
    {
        $this->reservaId = $reservaId;

        return $this;
    }

    /**
     * Get reservaId
     *
     * @return integer
     */
    public function getReservaId()
    {
        return $this->reservaId;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set horariosServicio
     *
     * @param \AppBundle\Entity\HorariosServicio $horariosServicio
     *
     * @return HorariosReserva
     */
    public function setHorariosServicio(\AppBundle\Entity\HorariosServicio $horariosServicio = null)
    {
        $this->horariosServicio = $horariosServicio;

        return $this;
    }

    /**
     * Get horariosServicio
     *
     * @return \AppBundle\Entity\HorariosServicio
     */
    public function getHorariosServicio()
    {
        return $this->horariosServicio;
    }

    /**
     * Set reserva1
     *
     * @param \AppBundle\Entity\Reserva $reserva1
     *
     * @return HorariosReserva
     */
    public function setReserva1(\AppBundle\Entity\Reserva $reserva1 = null)
    {
        $this->reserva1 = $reserva1;

        return $this;
    }

    /**
     * Get reserva1
     *
     * @return \AppBundle\Entity\Reserva
     */
    public function getReserva1()
    {
        return $this->reserva1;
    }
}


<?php

namespace console\seeder\helpers;

use Faker\Provider\DateTime;

trait CreatedAtUpdatedAt
{

    protected $createdAt;
    protected $updatedAt;
    /**
     * @var DateTime
     */
    private $generated;

    public function generate($start = '-90 days')
    {
        $this->generated = DateTime::dateTimeBetween($start);
        $this->createdAt = $this->generated->getTimestamp();

        $this->updatedAt = DateTime::dateTimeBetween($this->generated, 'now')->getTimestamp();
        return $this->createdAt;
    }

    public function newUpdatedAt($end = 'now')
    {
        return DateTime::dateTimeBetween($this->generated, $end)->getTimestamp();
    }
}
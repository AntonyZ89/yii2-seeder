<?php

namespace mootensai\seeder\helpers;

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
        $this->createdAt = $this->generated->format('Y-m-d H:i:s');

        $this->updatedAt = DateTime::dateTimeBetween($this->generated, 'now')->format('Y-m-d H:i:s');
        return $this->createdAt;
    }

    public function newUpdatedAt($end = 'now')
    {
        return DateTime::dateTimeBetween($this->generated, $end)->format('Y-m-d H:i:s');
    }
}

<?php
namespace App\Entity;

interface EventLoggableInterface
{
    public function getId(): ?int;
    public function getEventLogPrefix(): string;
}

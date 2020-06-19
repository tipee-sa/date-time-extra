<?php

namespace Gammadia\DateTimeExtra\Api;

interface IsoInterval
{
    public static function between($start, $end): self;

    public function getStart(): Temporal;
    public function getEnd(): Temporal;

    public function withStart(Temporal $t);
    public function withEnd(Temporal $t);

    public function isEmpty(): bool;

    public function isBefore(Temporal $t): bool;
    public function isBeforeInterval(self $other): bool;

    public function isAfter(Temporal $t): bool;
    public function isAfterInterval(self $other): bool;

    public function contains(Temporal $t): bool;
    public function containsInterval(self $other): bool;

    public function collapse(): self;
    public function equals(self $other): bool;
    public function toString(): string;

    public function precedes(self $other): bool;
    public function precededBy(self $other): bool;

    public function meets(self $other): bool;
    public function metBy(self $other): bool;

    public function overlaps(self $other): bool;
    public function overlappedBy(self $other): bool;

    public function finishes(self $other): bool;
    public function finishedBy(self $other): bool;

    public function starts(self $other): bool;
    public function startedBy(self $other): bool;

    public function encloses(self $other): bool;
    public function enclosedBy(self $other): bool;

    public function intersects(self $other): bool;

    public function findIntersection(self $other): self;

    public function abuts(self $other): bool;
}

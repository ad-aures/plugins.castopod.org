<?php

declare(strict_types=1);

/**
 * @see https://pestphp.com/docs/arch-testing#content-php
 */
arch()
    ->preset()
    ->php();

/**
 * @see https://pestphp.com/docs/arch-testing#content-security
 */
arch()
    ->preset()
    ->security();

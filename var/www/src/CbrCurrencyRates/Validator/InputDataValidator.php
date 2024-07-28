<?php

declare(strict_types=1);

namespace CbrCurrencyRates\Validator;

use CbrCurrencyRates\Exception\BadInputException;
use DateTimeImmutable;

class InputDataValidator
{
    /**
     * @param string $data
     * @throws BadInputException
     */
    public function validateDate(string $data)
    {
        $dateObject = DateTimeImmutable::createFromFormat('d/m/Y', $data);

        if(!$dateObject || $dateObject->format('d/m/Y') !== $data) {
            throw new BadInputException('Wrong date format!');
        }
    }

    /**
     * @param string $currencyCode
     * @throws BadInputException
     */
    public function validateCurrencyCode(string $currencyCode)
    {
        if (empty($currencyCode)) {
            throw new BadInputException('Empty currency code!');
        }
    }
}
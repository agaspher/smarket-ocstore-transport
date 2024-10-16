<?php

declare(strict_types=1);

namespace App\Import;

use App\Stats;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validation;

class ImportValidator
{
    public function validate(array $targets, ?Stats $stats = null): ConstraintViolationList
    {
        $errors = [];
        $validator = Validation::createValidatorBuilder()
            ->enableAnnotationMapping()
            ->getValidator();

        foreach ($targets as $target) {
            $errors = $validator->validate($target);

            if ($errors->count() > 0 && $stats) {
                $stats->addError([$target->getEntityId() => (string)$errors]);
            }
        }

        if (is_array($errors)) {
            $errors = new ConstraintViolationList($errors);
        }

        return $errors;
    }
}

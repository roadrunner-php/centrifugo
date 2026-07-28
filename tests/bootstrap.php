<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

/**
 * google/protobuf 4.x moved `RepeatedField` out of the `Internal` namespace into the public
 * `Google\Protobuf` one and left a `class_alias()` for the old FQN at the bottom of the new file.
 * That alias is only registered once the new class has been loaded, so autoloading the old FQN
 * on its own fails. Tests therefore reference the public FQN, which we back-fill on protobuf 3.x.
 *
 * `MapField` has not been moved and still lives in `Google\Protobuf\Internal` in every version.
 */
if (!\class_exists(\Google\Protobuf\RepeatedField::class)) {
    \class_alias(\Google\Protobuf\Internal\RepeatedField::class, \Google\Protobuf\RepeatedField::class);
}

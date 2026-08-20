<?php

declare(strict_types=1);

namespace Goosialize\Cookies\Consent;

enum ConsentLifecycleStatus: string
{
    case Valid = 'valid';
    case Missing = 'missing';
    case Malformed = 'malformed';
    case VersionMismatch = 'version_mismatch';
    case Expired = 'expired';
    case FutureTimestamp = 'future_timestamp';
}

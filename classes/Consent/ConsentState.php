<?php

declare(strict_types=1);

namespace Goosialize\Cookies\Consent;

enum ConsentState: string
{
    case Unknown = 'unknown';
    case AcceptedAll = 'accepted_all';
    case RejectedOptional = 'rejected_optional';
    case Custom = 'custom';
}

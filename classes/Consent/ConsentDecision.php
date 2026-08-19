<?php

declare(strict_types=1);

namespace Goosialize\Cookies\Consent;

enum ConsentDecision: string
{
    case AcceptAll = 'accept_all';
    case RejectOptional = 'reject_optional';
    case Custom = 'custom';
}

<?php

declare(strict_types=1);

namespace Goosialize\Cookies\Service;

enum StorageType: string
{
    case Cookie = 'cookie';
    case LocalStorage = 'local_storage';
    case SessionStorage = 'session_storage';
    case IndexedDb = 'indexed_db';
}

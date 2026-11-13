<?php
function isDuplicateDatabaseError(Throwable $error): bool {
    if (!($error instanceof PDOException)) {
        return false;
    }

    return ($error->errorInfo[0] ?? $error->getCode()) === '23505';
}

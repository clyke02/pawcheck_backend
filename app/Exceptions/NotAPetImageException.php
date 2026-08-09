<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Dilempar saat layanan prediksi menolak foto karena tidak dikenali
 * sebagai anjing atau kucing.
 */
class NotAPetImageException extends RuntimeException
{
    public function __construct(string $message = 'Foto tidak dikenali sebagai anjing atau kucing.')
    {
        parent::__construct($message);
    }
}

<?php
declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Service;

use PhpSpec\ObjectBehavior;

class EncrypterSpec extends ObjectBehavior
{
    function it_encrypts_a_key()
    {
        $this->beConstructedWith('AES-256-OFB', 'key', 'key');
        $this->encrypt('666')->shouldReturn('q5r5');
    }

    function it_decrypts_a_key()
    {
        $this->beConstructedWith('AES-256-OFB', 'key', 'key');
        $this->decrypt('q5r5')->shouldReturn('666');
    }

    function it_encrypt_with_an_initializatuon_vector_length_inferior_to_sixteen()
    {
        $this->beConstructedWith('AES-256-OFB', 'key', 'key');
        $this->decrypt('q5r5')->shouldReturn('666');
    }

    function it_encrypt_with_an_initializatuon_vector_length_truncated_to_sixteen_characters()
    {
        $this->beConstructedWith('AES-256-OFB', 'key', '0000000000000key1');
        $this->decrypt('q5r5')->shouldReturn('666');
    }
}

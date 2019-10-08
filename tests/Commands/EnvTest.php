<?php

namespace Sven\ForgeCLI\Tests\Commands;

use Sven\ForgeCLI\Commands\Env\Update;
use Sven\ForgeCLI\Tests\TestCase;

class EnvTest extends TestCase
{
    /** @test */
    public function it_updates_env_from_given_file()
    {
        $filepath = 'test.env';
        file_put_contents($filepath, 'FOO=bar');

        $this->forge->shouldReceive()
            ->updateSiteEnvironmentFile('12345', '6789', 'FOO=bar');

        $this->command(Update::class)
            ->execute([
                'server' => '12345',
                'site' => '6789',
                '--file' => $filepath
            ]);

        unlink($filepath);
    }


    /** @test */
    public function it_updates_env_from_given_content()
    {
        $this->forge->shouldReceive()
            ->updateSiteEnvironmentFile('12345', '6789', 'FOO=bar');

        $this->command(Update::class)
            ->execute([
                'server' => '12345',
                'site' => '6789',
                '--file' => 'FOO=bar'
            ]);
    }
}

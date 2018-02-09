<?php

declare(strict_types=1);

/**
 * This file is part of Laravel Console Summary.
 *
 * (c) Nuno Maduro <enunomaduro@gmail.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace NunoMaduro\LaravelConsoleSummary;

use Illuminate\Console\Application;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableStyle;
use Symfony\Component\Console\Output\OutputInterface;
use NunoMaduro\LaravelConsoleSummary\Contracts\DescriberContract;

/**
 * This is an Laravel Console Summary Text Describer implementation.
 */
class Describer implements DescriberContract
{
    /**
     * {@inheritdoc}
     */
    public function describe(Application $application, OutputInterface $output): void
    {
        $this->describeTitle($application, $output)
            ->describeUsage($output)
            ->describeCommands($application, $output);

        $output->write("\n");
    }

    /**
     * Describes the application title.
     *
     * @param \Illuminate\Console\Application $application
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return \NunoMaduro\LaravelConsoleSummary\Contracts\DescriberContract
     */
    protected function describeTitle(Application $application, OutputInterface $output): DescriberContract
    {
        $output->write(
            "\n<fg=white;options=bold>{$application->getName()} </> <fg=green;options=bold>{$application->getVersion()}</>\n\n"
        );

        return $this;
    }

    /**
     * Describes the application title.
     *
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return \NunoMaduro\LaravelConsoleSummary\Contracts\DescriberContract
     */
    protected function describeUsage(OutputInterface $output): DescriberContract
    {
        $binary = ARTISAN_BINARY;
        $output->write("<fg=yellow;options=bold>USAGE:</> $binary <command> [options] [arguments]\n");

        return $this;
    }

    /**
     * Describes the application commands.
     *
     * @param \Illuminate\Console\Application $application
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return \NunoMaduro\LaravelConsoleSummary\Contracts\DescriberContract
     */
    protected function describeCommands(Application $application, OutputInterface $output): DescriberContract
    {
        $style = (new TableStyle())->setHorizontalBorderChar('')
            ->setVerticalBorderChar('')
            ->setCrossingChar('');

        $table = (new Table($output))->setStyle($style);

        $namespaces = collect($application->all())
            ->groupBy(
                function ($command) {
                    $nameParts = explode(':', $command->getName());

                    return isset($nameParts[1]) ? $nameParts[0] : '';
                }
            )
            ->toArray();

        ksort($namespaces);

        $list = [];

        collect($namespaces)->map(
            function ($commands) use (&$list) {
                $list[] = ['', ''];
                collect($commands)->each(
                    function ($command) use (&$list) {
                        $list[] = ["<fg=green>{$command->getName()}</>", $command->getDescription()];
                    }
                );
            }
        );

        $table->setRows($list)
            ->render();

        return $this;
    }
}
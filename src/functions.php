<?php declare(strict_types=1);

/**
 * Get an option from the command-line.
 *
 * @param string $name
 * @return mixed
 * @throws \InvalidArgumentException
 */
function get_option(string $name)
{
    $options = getopt("", ["$name:"]);

    if (!array_key_exists($name, $options)) {
        throw new InvalidArgumentException("Option <$name> was not found in commad-line list");
    }

    return $options[$name];
}

/**
 * Print a message in the command-line and exit the script.
 *
 * @param string $message
 * @param string $error
 * @return void
 */
function print_command_info(string $message, string $error): void
{
    echo "$error\n$message";
    die;
}

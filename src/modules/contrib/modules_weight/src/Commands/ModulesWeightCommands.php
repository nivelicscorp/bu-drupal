<?php

namespace Drupal\modules_weight\Commands;

use Consolidation\AnnotatedCommand\CommandData;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\InfoParserInterface;
use Drupal\Core\Extension\ModuleExtensionList;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\modules_weight\ModulesWeightInterface;
use Drush\Commands\DrushCommands;
use Drush\Exceptions\UserAbortException;
use Symfony\Component\Console\Input\InputOption;

/**
 * Modules Weight Drush Commands.
 */
class ModulesWeightCommands extends DrushCommands {

  const MODULES_WEIGHT_GREEN_OUTPUT = "\033[1;32;40m\033[1m%s\033[0m";
  const MODULES_WEIGHT_RED_OUTPUT = "\033[31;40m\033[1m%s\033[0m";
  const OPT = InputOption::VALUE_OPTIONAL;

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected ConfigFactoryInterface $configFactory;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected ModuleHandlerInterface $moduleHandler;

  /**
   * The info parser.
   *
   * @var \Drupal\Core\Extension\InfoParserInterface
   */
  protected InfoParserInterface $infoParser;

  /**
   * The module extension list.
   *
   * @var \Drupal\Core\Extension\ModuleExtensionList
   */
  protected ModuleExtensionList $moduleExtensionList;

  /**
   * The module weight service.
   *
   * @var \Drupal\modules_weight\ModulesWeightInterface
   */
  protected ModulesWeightInterface $modulesWeight;

  /**
   * Constructs a new ModulesWeightCommands object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Extension\InfoParserInterface $info_parser
   *   The info parser.
   * @param \Drupal\Core\Extension\ModuleExtensionList $extension_list_module
   *   The module extension list.
   * @param \Drupal\modules_weight\ModulesWeightInterface $modules_weight
   *   The modules weight.
   */
  public function __construct(ConfigFactoryInterface $config_factory,
                              ModuleHandlerInterface $module_handler,
                              InfoParserInterface $info_parser,
                              ModuleExtensionList $extension_list_module,
                              ModulesWeightInterface $modules_weight
  ) {
    parent::__construct();
    $this->configFactory = $config_factory;
    $this->moduleHandler = $module_handler;
    $this->infoParser = $info_parser;
    $this->moduleExtensionList = $extension_list_module;
    $this->modulesWeight = $modules_weight;
  }

  /**
   * Validate for mw-show-system-modules command.
   *
   * @param \Consolidation\AnnotatedCommand\CommandData $commandData
   *   The command data.
   *
   * @hook validate mw-show-system-modules
   *
   * @throws \Exception
   */
  public function validateWeightShowSystemModules(CommandData $commandData) {
    $args = $commandData->input()->getArguments();

    // Available options.
    $available_options = [
      'on',
      'off',
    ];

    // Check for correct argument.
    if (isset($args['arg']) && !in_array($args['arg'], $available_options)) {
      throw new \Exception(dt("You must specify as argument 'on' or 'off'"));
    }
  }

  /**
   * Configures if we can reorder the core modules.
   *
   * @param string $arg
   *   The status option (on, off).
   *
   * @command mw-show-system-modules
   * @aliases mw-ssm
   */
  public function moduleWeightShowSystemModules(string $arg) {
    // Getting an editable config because we will get and set a value.
    $config = $this->configFactory->getEditable('modules_weight.settings');
    // Getting the values from the config.
    $show_system_modules = $config->get('show_system_modules');

    // Giving colors to the messages.
    $activated = sprintf(ModulesWeightCommands::MODULES_WEIGHT_GREEN_OUTPUT, dt('Activated'));
    $disabled = sprintf(ModulesWeightCommands::MODULES_WEIGHT_RED_OUTPUT, dt('Disabled'));

    if ($arg) {
      [$value, $status] = $arg == 'on' ?
        [1, strtolower($activated)] :
        [0, strtolower($disabled)];

      // Is already configured?
      if ($show_system_modules == $value) {
        // If is configured stop the command execution with a warning message.
        $message = dt('The core modules reorder option is already @status.', ['@status' => $status]);
        $this->logger()->warning($message);
        // Returning here to stop the function execution.
        return;
      }

      // Saving the values in the config.
      $config->set('show_system_modules', $value);
      $config->save();

      $message = dt('You have @status the core modules reorder option.', ['@status' => $status]);
      $this->logger()->success($message);
    }
    else {
      $status = $show_system_modules ? $activated : $disabled;

      $message = dt('The core modules reorder option is: @status', ['@status' => $status]);
      $this->output()->writeln($message);
    }
  }

  /**
   * Validate for mw-reorder.
   *
   * @param \Consolidation\AnnotatedCommand\CommandData $commandData
   *   The commanda data.
   *
   * @hook validate mw-reorder
   *
   * @throws \Drush\Exceptions\UserAbortException
   */
  public function validateModuleWeightReorder(CommandData $commandData) {
    $args = $commandData->input()->getArguments();

    // Check for a valid or installed module machine name.
    if (!$this->moduleHandler->moduleExists($args['module'])) {
      throw new \Exception(dt('@module_name module machine name is invalid or is not installed.', ['@module_name' => $args['module']]));
    }
    // Check for integer number.
    if (isset($args['weight']) && !ctype_digit($args['weight'])) {
      throw new \Exception(dt('You must enter digits for the modules-weight.'));
    }
    // Getting the --force option.
    $force = $commandData->input()->getOption('force');
    // Getting the module info.
    $module = $this->moduleHandler->getModule($args['module']);
    $module = $this->infoParser->parse($module->getPathname());
    // Getting the config to know of we should show or not the core modules.
    $show_system_modules = $this->configFactory->get('modules_weight.settings')->get('show_system_modules');
    // Checking if we can reorder the Core modules.
    if (!$force && $module['package'] == 'Core' && !$show_system_modules) {
      if (!$this->io()->confirm(dt("You're trying to reorder a Core module but Modules Weight is not configured to allow it. Do you want to continue?"))) {
        throw new UserAbortException();
      }
    }
  }

  /**
   * Configures the modules weight.
   *
   * @param string $module
   *   The module machine name.
   * @param int|null $weight
   *   The module weight.
   * @param array $options
   *   The options.
   *
   * @command mw-reorder
   * @aliases mw-r
   * @options minus If the option is present the weight will be consider as a
   *  negative value. Read for more information
   *  https://drupal.stackexchange.com/q/246298/28275 .
   */
  public function moduleWeightReorder(string $module, int $weight = NULL, array $options = ['minus' => self::OPT]) {
    if ($module && $weight) {
      // Getting the --minus option.
      $minus = $options['minus'];
      // Applying the minus option.
      $weight = $minus ? -1 * $weight : $weight;
      // Setting the new weight.
      module_set_weight($module, $weight);
      // Printing the message.
      $message = dt('The module weight for @module_name was updated to @weight.',
        [
          '@module_name' => $module,
          '@weight' => $weight,
        ]);
      $this->logger()->success($message);
    }
    else {
      // Searching for the module weigth.
      // Getting the list of installed modules from the config.
      $installed_modules = $this->configFactory->get('core.extension')->get('module') ?: [];
      if (!isset($installed_modules[$module])) {
        // If Module is not enabled.
        $message = dt('The module "@module_name" is not enabled.', ['@module_name' => $module]);
        $this->logger()->warning($message);
        // Returning here to stop the function execution.
        return;
      }
      // Getting the module weight.
      $weight = $installed_modules[$module];
      // Getting the module name.
      $module_name = $this->moduleExtensionList->getName($module);
      // Creating the array with the sustitution values.
      $values = [
        '@module_name' => $module_name,
        '@machine_name' => $module,
        '@weight' => $weight,
      ];
      $message = dt('The weight of the @module_name [@machine_name] module is: @weight', $values);
      $this->output()->writeln($message);
    }
  }

  /**
   * Shows the modules weight list.
   *
   * @command drush:mw-list
   * @aliases mw-l
   * @options force If the option is present the core modules will be shown even
   * if the option to allow it is disabled.
   */
  public function moduleWeightList(array $options = ['force' => self::OPT]) {
    $result = [];

    // Getting the --force option.
    $force = (bool) $options['force'];

    // If we don't force we need to check the configuration variable to know if
    // we should show or not the core modules.
    $show_core_modules = $force ?: $this->configFactory->get('modules_weight.settings')->get('show_system_modules');

    // Getting the module list.
    $modules = $this->modulesWeight->getModulesList($show_core_modules);
    // Iterate over each of the modules.
    foreach ($modules as $filename => $module) {
      // The rows info.
      $row = [];
      // Module name.
      $row['name'] = $module['name'];
      // Module machine name.
      $row['machine_name'] = $filename;
      // Module weight.
      $row['weight'] = $module['weight'];
      // Module package.
      $row['package'] = $module['package'];

      $result[] = $row;
    }

    return $result;
  }

}

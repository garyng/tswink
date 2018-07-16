<?php

namespace TsWink\Classes;

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\IntegerType;
use Doctrine\DBAL\Types\StringType;
use Mustache_Engine;
use Mustache_Loader_FilesystemLoader;
use Mustache_Logger_StreamLogger;

Class TswinkGenerator extends Generator
{
  /** @var TypeSimplifier */
  private $typeSimplifier;

  /** @var DefaultValueCreator */
  private $defaultValueCreator;

  /** @var Table */
  private $table;

  /** @var string */
  private $destination;

  /** @var boolean */
  private $defaultValue;

  /** @var Mustache_Engine */
  private $mustache;

  public function __construct()
  {
    parent::__construct();

    $this->typeSimplifier = new TypeSimplifier;
    $this->defaultValueCreator = new DefaultValueCreator;

    if (function_exists('config')) {
      $this->destination = base_path(config('tswink.ts_classes_destination'));
      $this->defaultValue = config('tswink.add_default_value');
    }

    $this->mustache = new Mustache_Engine([
      'loader' => new Mustache_Loader_FilesystemLoader(
        dirname(__FILE__) . '/../../templates',
        ['extension' => '.markdown']
      ),
      'escape' => function($value) {
        return $value;
      },
      'logger' => new Mustache_Logger_StreamLogger('php://stdout')
    ]);
  }

  public function generate()
  {
    /** @var Table $table */
    foreach ($this->tables as $table) {
      $this->table = $table;
      $this->processTable();
    }
  }

  private function processTable()
  {
    $this->writeFile($this->fileName() . ".ts", $this->getClassContent());
  }

  private function fileName()
  {
    return str_singular(kebab_case(camel_case($this->table->getName())));
  }

  private function getClassContent()
  {

    $data = [
      'name' => $this->getTableNameForClassFile(),
      'fields' => (function(){
        $out = [];
        foreach ($this->table->getColumns() as $column){
          $out[] = [
            'name' => $column->getName(),
            'type' => $this->getSimplifiedType($column),
            'default-value' => $this->getDefaultValue($column)
          ];
        }
        return $out;
      })()
    ];
    return $this->mustache->render('class', $data);
  }

  private function getTableNameForClassFile()
  {
    return ucfirst($this->singularFromTableName());
  }

  private function singularFromTableName()
  {
    return str_singular(camel_case($this->table->getName()));
  }

  private function getSimplifiedType(Column $column)
  {
    return $this->typeSimplifier->simplify($column);
  }

  private function getDefaultValue(Column $column)
  {
    return $this->defaultValueCreator->getDefault($column);
  }

  private function writeFile($fileName, $tsClass)
  {
    if (!file_exists($this->destination)) {
      mkdir($this->destination, 077, true);
    }

    $filePath = "{$this->destination}/$fileName";

    $file = fopen($filePath, "w");
    fwrite($file, $tsClass);
    fclose($file);
  }

  public function getDestination()
  {
    return $this->destination;
  }

  public function setDestination($destination)
  {
    $this->destination = $destination;
  }
}

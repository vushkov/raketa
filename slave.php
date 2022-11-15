<?php

namespace slaveSpace;

//класс slave описывает свойства раба
class slave
{
  public string $nickname, $sex, $skinColor, $whereCaught, $descripion;
  public int $age, $weight, $hourRate, $cost;

  // используем конструктор для передачи свойств раба при создании объекта
  public function __construct(
    string $nicknameC,
    string $sexC,
    int $ageC,
    int $weightC,
    string $skinColorC,
    string $whereCaughtC,
    string $descripionC,
    int $hourRateC,
    int $costC
  )
  {
    $this->nickname = $nicknameC;
    $this->sex = $sexC;
    $this->age = $ageC;
    $this->weight = $weightC;
    $this->skinColor = $skinColorC;
    $this->whereCaught = $whereCaughtC;
    $this->descripion = $descripionC;
    $this->hourRate = $hourRateC;
    $this->cost = $costC;
  }
}
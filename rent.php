<?php

namespace slaveSpace;

include 'slave.php';

// определяем функцию rentSlave, которая будет описывать логику аренды раба
function rentSlave(
    slave $slave,
    int $dayStart,
    int $monthStart,
    int $yearStart,
    int $hourStart,
    int $minuteStart,
    int $dayEnd,
    int $monthEnd,
    int $yearEnd,
    int $hourEnd,
    int $minuteEnd,
    int $curRentStart,
    int $curRentEnd,
    bool $isVIP,
    String $task
)
{
    // Объявляем переменную, далее в коде ей будет присвоено значение, возвращаемое вложенной функцией calcTotalCost()
    $totalCost = 0;

    // Реализуем округление введенных данных

    if ($minuteStart <= 30) {
        $minuteStart = 0;
    } else {
        $minuteStart = 0;
        $hourStart++;
    }

    if ($minuteEnd >= 30) {
        $minuteEnd = 0;
        $hourEnd++;
    } else {
        $minuteEnd = 0;
    }

    print_r("Введены данные (округлены до часов):\n" . sprintf('From: %02d.%02d.%d %02d:00 To: %02d.%02d.%d %02d:00 ', $dayStart, $monthStart, $yearStart, $hourStart, $dayEnd, $monthEnd, $yearEnd, $hourEnd));

    // Переводим полученные от пользователя данные в UnixTime
    $newRentStart = mktime($hourStart, 0, 0, $monthStart, $dayStart, $yearStart);
    $newRentEnd = mktime($hourEnd, 0, 0, $monthEnd, $dayEnd, $yearEnd);

    // Проверяем, чтобы время конца периода было всегда позже, чем его начало, если это не так - останавливаем выполнение функции
    if ($newRentStart >= $newRentEnd) {
        print_r("\nВремя окончания периода должно быть позже его начала!");
        return;
    }

    // Создаем функцию calcTotalCost(), которая в нужный момент будет высчитывать итоговую стоимость аренды
    function calcTotalCost($newRentEnd, $newRentStart, $yearStart, $yearEnd, $monthStart, $monthEnd, $dayStart, $dayEnd, $hourStart, $hourEnd, $slave)
    {
        // Подсчитываем общее количество часов аренды за выбранный период
        $totalHours = ($newRentEnd - $newRentStart) / 3600;

        // Если период аренды находится в пределах одного дня, то просто высчитываем общее количество часов и умножаем их на ставку, если нет - высчитываем часы в первый и последний дни, а также часы в целые дни
        if ($yearStart === $yearEnd && $monthStart === $monthEnd && $dayStart === $dayEnd) {
            if ($totalHours > 16) {
                $totalHours = 16;
            }
            $totalCost = $totalHours * $slave->hourRate;
        } else {
            // Делаем проверку рабочего времени раба = 16 часам, в первый день аренды
            $hoursStartDay = 24 - $hourStart;

            // Ограничиваем рабочий день 16-тью часами в первый день аренды
            if ($hoursStartDay > 16) {
                $hoursStartDay = 16;
            }

            // Делаем проверку рабочего времени раба = 16 часам, в последний день аренды
            $hoursEndDay = $hourEnd;

            // Ограничиваем рабочий день 16-тью часами в последний день аренды
            if ($hoursEndDay > 16) {
                $hoursEndDay = 16;
            }

            // Вычисляем количество полных дней
            $entireDays = ($totalHours - (24 - $hourStart) - $hourEnd) / 24;

            // Вычисляем общую стоимость за период аренды
            $totalCost = $hoursStartDay * $slave->hourRate + $entireDays * 16 * $slave->hourRate + $hoursEndDay * $slave->hourRate;
        }
        return $totalCost;
    }

    // Производим проверку пересечения диапазонов аренды. Сразу исключаем никаким образом не пересекающиеся периоды.
    if (($newRentStart < $curRentStart && $newRentEnd < $curRentStart) || ($newRentStart > $curRentEnd && $newRentEnd > $curRentEnd)) {

        $totalCost = calcTotalCost($newRentEnd, $newRentStart, $yearStart, $yearEnd, $monthStart, $monthEnd, $dayStart, $dayEnd, $hourStart, $hourEnd, $slave);

        print_r("\nВы успешно арендовали раба на период с " . date("d.m.Y H:00", $newRentStart) . " по " . date("d.m.Y H:00", $newRentEnd) . "!");
        print_r("\nЗадача: " . $task);
        print_r("\nСтоимость за период составит (золотых монет): " . $totalCost);
    } else {
        // Если клиент является VIP, то аренда ведется не взирая на уже занятые периоды
        if ($isVIP === true) {
            $totalCost = calcTotalCost($newRentEnd, $newRentStart, $yearStart, $yearEnd, $monthStart, $monthEnd, $dayStart, $dayEnd, $hourStart, $hourEnd, $slave);

            print_r("\nВы являетесь VIP покупателем!\nВы успешно арендовали раба на период с " . date("d.m.Y H:00", $newRentStart) . " по " . date("d.m.Y H:00", $newRentEnd) . "!");
            print_r("\nЗадача: " . $task);
            print_r("\nСтоимость за период составит (золотых монет): " . $totalCost);
        } else {
            print_r("\nОшибка аренды раба!\n");

            // Проверка, если новый период аренды начинаетcя раньше или одновременно с существующим, а заканчивается в пределах существующего или одновременно с ним
            if ($newRentStart <= $curRentStart && $newRentEnd <= $curRentEnd) {
                print_r("Период с " . date("d.m.Y H:00", $curRentStart) . " до " . date("d.m.Y H:00", $newRentEnd) . " уже занят!");

                // Проверка, если новый период аренды начинается раньше существующего или одновременно с ним, а заканчивается строго позже него
            } elseif ($newRentStart <= $curRentStart && $newRentEnd > $curRentEnd) {
                print_r("Период с " . date("d.m.Y H:00", $curRentStart) . " до " . date("d.m.Y H:00", $curRentEnd) . " уже занят!");

                // Проверка, если новый период начинается позже, чем существующий период, а заканчивается одновременно с существующим или раньше него
            } elseif ($newRentStart > $curRentStart && $newRentEnd <= $curRentEnd) {
                print_r("Период с " . date("d.m.Y H:00", $newRentStart) . " до " . date("d.m.Y H:00", $newRentEnd) . " уже занят!");

                // Проверка, если новый период начинается позже, чем существующий период и заканчивается строго позже существующего периода
            } elseif ($newRentStart > $curRentStart && $newRentEnd > $curRentEnd) {
                print_r("Период с " . date("d.m.Y H:00", $newRentStart) . " до " . date("d.m.Y H:00", $curRentEnd) . " уже занят!");

            } else {
                print_r("Ошибка аренды раба! Скорее всего программист ошибся при подсчете периодов аренды =(");
            }
        }
    }
}

// Создаем объект $slave, который является экземпляром класса slave.
// Параметры раба берем из БД. То есть, пользователь выбрал раба в GUI --> мы получили {id} раба и тип задачи ('Уборка') -->
// --> выбираем из таблицы slaves все нужные параметры этого раба запросом SELECT * FROM slaves WHERE id={id}; --> передаем параметры в объект $slave
$slave = new slave('Tiny', 'M', 39, 83, 'White', 'Arizona', 'Calm', 3, 7000);

// Представим, что у нас в БД есть таблица periods, там хранятся все уже занятые периоды всех рабов.
// Выбираем из этой таблицы занятые периоды для конкретного раба, для упрощения представим, что период один (Если будет много уже занятых периодов, можно будет сделать цикл и проверять все занятые периоды аренды на пересечение с желаемым)
// Храним в Unixtime
$curRentStart = 1464760800; // 01.06.2016, 11:00:00
$curRentEnd = 1465120800; // 05.06.2016, 15:00:00

// Данные пользователей(хозяев), храним в таблице owners. При аренде раба делаем запрос в БД, чтобы узнать является ли хозяин VIP`ом
$isVIP = false;

// Вызываем функцию rentSlave() передавая ей параметры, полученные из БД и от пользователя, который пытается арендовать раба через GUI
rentSlave($slave, 4, 6, 2016, 12, 40, 9, 6, 2016, 15, 12, $curRentStart, $curRentEnd, $isVIP, 'Уборка');
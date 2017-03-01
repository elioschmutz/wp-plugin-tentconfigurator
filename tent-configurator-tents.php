<?php

if ( ! defined( 'WPINC' ) ) {
    die;
}

$tent_data = array(
    array('id' => 1, 'title' => 'keine genaue Zeltvorstellung', 'price' => 0, 'length' => 0, 'width' => 0, 'minPersons' => 0, 'maxPersons' => 0),
    array('id' => 2, 'title' => 'Bierzelt inkl. 4 Tische', 'price' => 400, 'length' => 6, 'width' => 4, 'minPersons' => 32, 'maxPersons' => 40),
    array('id' => 3, 'title' => 'Bierzelt inkl. 6 Tische', 'price' => 500, 'length' => 8, 'width' => 4, 'minPersons' => 48, 'maxPersons' => 60),
    array('id' => 4, 'title' => 'Bierzelt inkl. 8 Tische', 'price' => 600, 'length' => 10, 'width' => 4, 'minPersons' => 64, 'maxPersons' => 80),
    array('id' => 5, 'title' => 'Bierzelt inkl. 12 Tische', 'price' => 750, 'length' => 12, 'width' => 4, 'minPersons' => 96, 'maxPersons' => 120),
    array('id' => 6, 'title' => 'Festzelt klein 4x10', 'price' => 350, 'length' => 4, 'width' => 10, 'minPersons' => 1, 'maxPersons' => 100),
    array('id' => 7, 'title' => 'Festzelt klein 8x10', 'price' => 480, 'length' => 8, 'width' => 10, 'minPersons' => 90, 'maxPersons' => 150),
    array('id' => 8, 'title' => 'Festzelt klein 12x10', 'price' => 600, 'length' => 12, 'width' => 10, 'minPersons' => 130, 'maxPersons' => 200),
    array('id' => 9, 'title' => 'Festzelt klein 16x10', 'price' => 800, 'length' => 16, 'width' => 10, 'minPersons' => 170, 'maxPersons' => 250),
    array('id' => 10, 'title' => 'Festzelt klein 20x10', 'price' => 1000, 'length' => 20, 'width' => 10, 'minPersons' => 220, 'maxPersons' => 300),
    array('id' => 11, 'title' => 'Festzelt klein 24x10', 'price' => 1200, 'length' => 24, 'width' => 10, 'minPersons' => 280, 'maxPersons' => 400),
    array('id' => 12, 'title' => 'Festzelt klein 28x10', 'price' => 1400, 'length' => 28, 'width' => 10, 'minPersons' => 360, 'maxPersons' => 450),
    array('id' => 13, 'title' => 'Festzelt klein 32x10', 'price' => 1600, 'length' => 32, 'width' => 10, 'minPersons' => 400, 'maxPersons' => 520),
    array('id' => 14, 'title' => 'Festzelt gross', 'price' => 1200, 'length' => 12, 'width' => 20, 'minPersons' => 200, 'maxPersons' => 400),
    array('id' => 15, 'title' => 'Festzelt gross', 'price' => 1600, 'length' => 16, 'width' => 20, 'minPersons' => 350, 'maxPersons' => 500),
    array('id' => 16, 'title' => 'Festzelt gross', 'price' => 2000, 'length' => 20, 'width' => 20, 'minPersons' => 400, 'maxPersons' => 600),
    array('id' => 17, 'title' => 'Festzelt gross', 'price' => 2400, 'length' => 24, 'width' => 20, 'minPersons' => 550, 'maxPersons' => 700),
    array('id' => 18, 'title' => 'Festzelt gross', 'price' => 2800, 'length' => 28, 'width' => 20, 'minPersons' => 600, 'maxPersons' => 800),
    array('id' => 19, 'title' => 'Festzelt gross', 'price' => 3000, 'length' => 32, 'width' => 20, 'minPersons' => 750, 'maxPersons' => 900),
    array('id' => 20, 'title' => 'Festzelt gross', 'price' => 3240, 'length' => 36, 'width' => 20, 'minPersons' => 800, 'maxPersons' => 1000),
    array('id' => 21, 'title' => 'Festzelt gross', 'price' => 3600, 'length' => 40, 'width' => 20, 'minPersons' => 950, 'maxPersons' => 1100),
    array('id' => 22, 'title' => 'Festzelt gross', 'price' => 3960, 'length' => 44, 'width' => 20, 'minPersons' => 1000, 'maxPersons' => 1200),
    array('id' => 23, 'title' => 'Festzelt gross', 'price' => 4320, 'length' => 48, 'width' => 20, 'minPersons' => 1150, 'maxPersons' => 1300),
    array('id' => 24, 'title' => 'Festzelt gross', 'price' => 4680, 'length' => 52, 'width' => 20, 'minPersons' => 1200, 'maxPersons' => 1400),
    array('id' => 25, 'title' => 'Festzelt gross', 'price' => 5040, 'length' => 56, 'width' => 20, 'minPersons' => 1350, 'maxPersons' => 1500),
    array('id' => 26, 'title' => 'Festzelt gross', 'price' => 5400, 'length' => 60, 'width' => 20, 'minPersons' => 1400, 'maxPersons' => 1600),
    array('id' => 27, 'title' => 'Festzelt gross', 'price' => 5760, 'length' => 64, 'width' => 20, 'minPersons' => 1550, 'maxPersons' => 1700),
    array('id' => 28, 'title' => 'Festzelt gross', 'price' => 6120, 'length' => 68, 'width' => 20, 'minPersons' => 1600, 'maxPersons' => 1800),
    array('id' => 29, 'title' => 'Festzelt gross', 'price' => 6480, 'length' => 72, 'width' => 20, 'minPersons' => 1750, 'maxPersons' => 1900),
    array('id' => 30, 'title' => 'Festzelt gross', 'price' => 6840, 'length' => 76, 'width' => 20, 'minPersons' => 1800, 'maxPersons' => 2000),
    array('id' => 31, 'title' => 'Festzelt gross', 'price' => 7200, 'length' => 80, 'width' => 20, 'minPersons' => 1950, 'maxPersons' => 2100),
);

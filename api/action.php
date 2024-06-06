<?php
function locked_file_get_contents($filename)
{
    $lockFile = $filename . '.lock';
    $lockHandle = fopen($lockFile, 'w');

    while (!flock($lockHandle, LOCK_EX)) {
        echo "OK";
        usleep(1000000); // Attendez 1 seconde
    }

    $content = file_get_contents($filename);

    flock($lockHandle, LOCK_UN);
    fclose($lockHandle);

    return $content;
}

function locked_file_put_contents($filename, $data)
{
    $lockFile = $filename . '.lock';
    $lockHandle = fopen($lockFile, 'w');

    while (!flock($lockHandle, LOCK_EX)) {
        echo "OK";
        usleep(1000000); // Attendez 1 seconde
    }

    file_put_contents($filename, $data);

    flock($lockHandle, LOCK_UN);
    fclose($lockHandle);
}

if (!empty($_GET['id'])) {
    if (file_exists("./tmp/game_" . $_GET["id"] . ".json") && $_GET["command"] !== "joinParty") {
        $JSON = json_decode(locked_file_get_contents("./tmp/game_" . $_GET["id"] . ".json"), true);
        function save()
        {
            global $JSON;
            locked_file_put_contents("./tmp/game_" . $_GET["id"] . ".json", json_encode($JSON));
        }
        if ($_GET["command"] === "setLife") {
            if (!empty($_GET["command"]) && !empty($_GET["commandUser"]) && !empty($_GET["commandArg"])) {
                if (!empty($_GET["commandArg2"])) {
                    if (isset($JSON[$_GET["commandUser"]])) {
                        if (!empty($JSON[$_GET["commandUser"]][$_GET["commandArg"]])) {
                            if ((int) $_GET["commandArg2"] <= 0) {
                                unset($JSON[$_GET["commandUser"]][$_GET["commandArg"]]);
                            } else {
                                $JSON[$_GET["commandUser"]][$_GET["commandArg"]] = (int) $_GET["commandArg2"];
                            }
                            echo "Success";
                            save();
                        } else {
                            $JSON[$_GET["commandUser"]][$_GET["commandArg"]] = (int) $_GET["commandArg2"];
                            echo "Success";
                            save();
                        }
                    } else {
                        echo "User not found";
                    }
                } else {
                    echo "empty CommandArg2";
                }
            } else {
                echo "Empty command/commandUser/commandArg";
            }
        }
        if ($_GET["command"] === "updateTimeStamp") {
            if (!empty($_GET["command"])) {
                $JSON["GameData"]["start"] = (string) time();
                save();
            }
        }
        foreach ($JSON as $key => $value) {
            foreach ($value as $hero => $power) {
                if ($power <= 1) {
                    unset($JSON[$key][$hero]);
                }
            }
        }
        save();
    }
    if ($_GET["command"] === "startBot") {
        locked_file_put_contents("./tmp/game_" . $_GET["id"] . ".json", '{"Bot": {},"' . (string) $_GET["u"] . '": {},"GameData": { "start": "' . (string) time() . '" }}');
        echo locked_file_get_contents("../cards.json");
    }
    if ($_GET["command"] === "initParty") {
        locked_file_put_contents("./tmp/game_" . $_GET["id"] . ".json", "WATING//" . (string) $_GET["u"] . "//" . (string) $_GET["d"]);
        echo "Success";
    }
    if ($_GET["command"] === "joinParty") {
        if (file_exists("./tmp/game_" . $_GET["id"] . ".json")) {
            $AllContent = locked_file_get_contents("./tmp/game_" . $_GET["id"] . ".json");
            if (!empty(explode("//", $AllContent)[1]) && json_decode($AllContent) === null) {
                $content = explode("//", $AllContent)[1];
                $user1 = trim($content);
                $user2 = $_GET["u"];
                locked_file_put_contents("./tmp/game_" . $_GET["id"] . ".json", '{"' . $user1 . '": {},"' . $user2 . '": {},"GameData": { "start": "' . (string) time() . '" }}');

                echo $user1 . "//Success//" . explode("//", $AllContent)[2];

            } else {
                echo "Party earady started, or joined";
            }
        } else {
            echo "Party not found";

        }
    }
} else {
    if ($_GET["command"]) {
        if ($_GET["command"] === "getTime") {
            echo "<GET>" . (string) time();
        }
    } else {
        echo "NoID";
    }
}
?>

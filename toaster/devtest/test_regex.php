<?php
// The "i" after the pattern delimiter indicates a case-insensitive search
if (preg_match("@.*skype.com@", "skype.com")) {
    echo "A match was found.";
} else {
    echo "A match was not found.";
}
?>

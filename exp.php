<?php
define("file", __DIR__ . "/exp.json");
echo "Hello welcome to expense tracker!\n";

function loadExpenses() // function to load expenses from json file
{
    if (!file_exists(file)) {
        return []; // return empty if no file
    }
    $json = file_get_contents(file);
    $expenses = json_decode($json, true); // decode json to associative array
    return $expenses ?: []; // return expenses or empty array if null
}
function saveExpenses($expenses) // function to save expenses to json file
{
    file_put_contents(file, json_encode($expenses, JSON_PRETTY_PRINT)); //
}
function nextId($expenses) // auto incrementing id function
{
    if (empty($expenses))
        return 1; // if no expenses, start with id 1
    $ids = array_column($expenses, 'id'); // get all existing ids
    return max($ids) + 1;
}
function help() // help function to display commands
{
    echo "This is the help menu: 
  -  add --desc <desc> --amt <amt> --date <date(YYYY-MM-DD)> Add expense 
  -  view --id <id> View expenses 
  -  delete --id <id> Delete expense 
  -  update --id <id> Update expense 
  -  exit Exit\n
";
}
function parseArgs($args)
{
    $options = [];
    for ($i = 0; $i < count($args); $i++) {
        $arg = $args[$i];
        if (substr($arg, 0, 2) === '--') {
            $key = substr($arg, 2);
            $value = $args[$i + 1] ?? true;
            if (substr($value, 0, 2) === '--') {
                $options[$key] = true;
            } else {
                $options[$key] = $value;
                $i++;
            }
        }
    }
    return $options;
}

$argv = $_SERVER['argv'];
$argc = $_SERVER['argc'];

$cmd = $argv[1] ?? "help";
$args = array_slice($argv, 2);
switch ($cmd) {
    case strtolower("add"):

        $options = parseArgs($args);
        $expenses = loadExpenses();
        $expense = [
            "id" => nextId($expenses),
            "desc" => $options['desc'] ?? "No description",
            "amt" => (int) ($options['amt'] ?? "0"),
            "date" => $options['date'] ?? date("Y-m-d"),
        ];
        $expenses[] = $expense;
        saveExpenses($expenses);
        echo "Expense added successfully!\n";
        break;
    case strtolower("view"):
        $options = parseArgs($args);
        if (empty($options['id'])) {
            echo "Please provide an ID to view.\n";
            break;
        }
        $id = (int) $options['id'];
        $expenses = loadExpenses();
        $found = null;
        foreach ($expenses as $expense) {
            if ($expense['id'] == $id) {
                $found = $expense;
                break;
            }
        }
        if ($found) {
            $amount = '$' . $found['amt'];
            printf(
                "Expense Details:\n-ID: %d\n-Date: %s\n-Description: %s\n-Amount: %s\n",
                $found['id'],
                $found['date'],
                $found['desc'],
                $amount
            );
        } else {
            echo "Expense with ID $id not found.\n";
        }
        break;
    case strtolower("delete"):
        $options = parseArgs($args);
        if (empty($options['id'])) {
            echo "Please provide an ID to delete.\n";
            break;
        }
        $id = (int) $options['id'];
        $expenses = loadExpenses();
        $newExpenses = array_filter($expenses, fn($expense) => $expense['id'] != $id);
        if (count($newExpenses) === count($expenses)) {
            echo "Expense with ID $id not found.\n";
        } else {
            saveExpenses(array_values($newExpenses));
            echo "Expense with ID $id deleted successfully.\n";
        }
        break;
    case strtolower("update"):
        $options = parseArgs($args);
        if (empty($options['id'])) {
            echo "Please provide an ID to update.\n";
            break;
        }
        $id = (int) $options['id'];
        $expenses = loadExpenses();
        $found = null;
        foreach ($expenses as $expense)
        {
            if ($expense['id'] == $id)
            {
                $found = $expense;
                break;
            }
        }
        if ($found) {
            $found['desc'] = $options['desc'] ?? $found['desc'];
            $found['amt'] = isset($options['amt']) ? (int) $options['amt'] : $found['amt'];
            $found['date'] = $options['date'] ?? $found['date'];

            foreach ($expenses as &$expense) {
                if ($expense['id'] == $id) {
                    $expense = $found;
                    break;
                }
            }
            unset($expense); // break reference
            echo "Expense with ID $id updated successfully.\n";
        }
        else {
            echo "Expense with ID $id not found.\n";
        }
        break;
    case strtolower("list"):
        $expenses = loadExpenses();
        if (empty($expenses)) {
            echo "No expenses found.\n";
        } else {
            echo "# ID  Date        Description             Amount\n";
            foreach ($expenses as $expense) {
                printf(
                    "# %-3d %-10s %-20s \$%d\n",
                    $expense['id'],
                    $expense['date'],
                    $expense['desc'],
                    $expense['amt']
                );
            }
        }
        break;
    case strtolower("help"):
        help();
        break;
    default:
        echo "Unknown command.\n";
        help();
        break;
}
?>
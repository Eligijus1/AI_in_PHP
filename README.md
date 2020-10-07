# AI_in_PHP
Some experiments with PHP and ANN.

## Handwritten number recognize test
- Download MNIST DB from http://yann.lecun.com/exdb/mnist/
- Put files to directory: AI_in_PHP/html/number_recognize/data/mnist/
- To start test run ```php main.php test_sigmoid``` from directory AI_in_PHP/html/number_recognize/

## Handwritten number recognize train
- Download MNIST DB from http://yann.lecun.com/exdb/mnist/
- Put files to directory: AI_in_PHP/html/number_recognize/data/mnist/
- To start test run ```php main.php train_sigmoid``` from directory AI_in_PHP/html/number_recognize/

## FANN test
- Download MNIST DB from http://yann.lecun.com/exdb/mnist/
- Put files to directory: AI_in_PHP/html/number_recognize/data/mnist/
- Run ```php main.php fann_generate_training_data_file``` from directory AI_in_PHP/html/number_recognize/, to generate FANN library required files.
- Run ```php main.php fann_train_sigmoid```, to train 150 epochs. Training network will be saved to file ```AI_in_PHP/html/number_recognize/data/fann/fann_mnist_sigmoid.net```
- Run ```php main.php fann_test_sigmoid fann_mnist_sigmoid.net``` file, to test results on generated network file from ```AI_in_PHP/html/number_recognize/data/fann/```

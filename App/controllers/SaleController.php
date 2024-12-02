<?php
use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();
session_start();
namespace controllers;
use config\conn; 
use models\Sale;
use models\Product;
use helpers\FlashMessage;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Exception\ApiErrorException;

class SaleController
{
    private $conn;

    public function __construct()
    {
        $this->conn = conn::getConnection();
        Stripe::setApiKey(getenv('STRIPE_KEY'));
    }

    public function createSale(array $productIds, $paymentMethodId)
    {
        try {
            $totalPrice = 0;
            $products = [];

            foreach ($productIds as $productId) {
                $product = $this->getProductById($productId);
                if ($product) {
                    $totalPrice += $product->getPrice();
                    $products[] = $product;
                } else {
                    FlashMessage::setMessage("Produto com ID $productId não encontrado.", 'error');
                    return;
                }
            }

            $paymentIntent = $this->createPaymentIntent($totalPrice);


            if ($paymentIntent->status !== 'succeeded') {
                FlashMessage::setMessage('Pagamento falhou. Tente novamente.', 'error');
                return;
            }

            $sale = new Sale();
            foreach ($products as $product) {
                $sale->addProduct($product, 1);
            }
            $sale->setTotalPrice($totalPrice);

            $saleId = $this->saveSaleToDatabase($sale, $products);

            FlashMessage::setMessage('Venda realizada com sucesso!', 'success');
        } catch (ApiErrorException $e) {
            FlashMessage::setMessage('Erro ao processar pagamento com Stripe: ' . $e->getMessage(), 'error');
        } catch (\Exception $e) {
            FlashMessage::setMessage('Erro ao realizar a venda: ' . $e->getMessage(), 'error');
        }
    }

    public function getAllSales()
    {
        try {
            $stmt = $this->conn->prepare('SELECT * FROM sales');
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_OBJ);
        } catch (\PDOException $e) {
            FlashMessage::setMessage('Erro ao recuperar as vendas: ' . $e->getMessage(), 'error');
            return [];
        }
    }

    public function getSaleById($id)
    {
        try {
            $stmt = $this->conn->prepare('SELECT * FROM sales WHERE id = ?');
            $stmt->bindParam(1, $id, \PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(\PDO::FETCH_OBJ);
        } catch (\PDOException $e) {
            FlashMessage::setMessage('Erro ao recuperar a venda: ' . $e->getMessage(), 'error');
            return null;
        }
    }

    private function getProductById($id)
    {
        try {
            $stmt = $this->conn->prepare('SELECT * FROM products WHERE id = ?');
            $stmt->bindParam(1, $id, \PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchObject(Product::class);
        } catch (\PDOException $e) {
            FlashMessage::setMessage('Erro ao recuperar o produto: ' . $e->getMessage(), 'error');
            return null;
        }
    }

    private function createPaymentIntent($totalPrice)
    {
        try {
            return PaymentIntent::create([
                'amount' => $totalPrice * 100,
                'currency' => 'BRL',
                'payment_method_types' => ['pix', 'card'],
            ]);
        } catch (ApiErrorException $e) {
            FlashMessage::setMessage('Erro ao criar PaymentIntent: ' . $e->getMessage(), 'error');
            throw $e;
        }
    }

    private function saveSaleToDatabase(Sale $sale, array $products)
    {
        try {
            $stmt = $this->conn->prepare('INSERT INTO sales (total_price) VALUES (?)');
            $stmt->bindParam(1, $sale->getTotalPrice(), \PDO::PARAM_STR);
            $stmt->execute();

            $saleId = $this->conn->lastInsertId();

            foreach ($products as $product) {
                $stmt = $this->conn->prepare('INSERT INTO sale_products (sale_id, product_id) VALUES (?, ?)');
                $stmt->bindParam(1, $saleId, \PDO::PARAM_INT);
                $stmt->bindParam(2, $product->getId(), \PDO::PARAM_INT);
                $stmt->execute();
            }

            return $saleId;
        } catch (\PDOException $e) {
            FlashMessage::setMessage('Erro ao salvar a venda: ' . $e->getMessage(), 'error');
        }
    }
}

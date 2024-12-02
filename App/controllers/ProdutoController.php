<?php
session_start();

namespace controllers;
use config\conn;  
use models\Product;
use PDOException;
use PDO;
use helpers\FlashMessage;

class ProductController
{

      protected $conn;

      public function __construct()
      {
            $this->conn = conn::getConnection();
      }

      public function index()
      {
            try {
                  $stmt = $this->conn->prepare('SELECT * FROM products');
                  $stmt->execute();
                  $products = $stmt->fetchAll(PDO::FETCH_OBJ);
                  return $products;
            } catch (PDOException $e) {
                  FlashMessage::setMessage('Houve um erro inesperado ao retornar a lista de produtos: ' . $e->getMessage(), 'error');
                  return [];
            }
      }

      public function showProduct($id)
      {
            try {
                  $stmt = $this->conn->prepare('SELECT * FROM products WHERE id = ?');
                  $stmt->bindParam(1, $id, PDO::PARAM_INT);

                  if (!$stmt->execute()) {
                        FlashMessage::setMessage('Houve um erro inesperado ao recuperar o produto selecionado', 'error');
                        return null;
                  }

                  $product = $stmt->fetch(PDO::FETCH_OBJ);

                  if (!$product) {
                        FlashMessage::setMessage('Produto não encontrado', 'error');
                        return null;
                  }

                  return $product;
            } catch (PDOException $e) {
                  FlashMessage::setMessage('Erro ao acessar o banco de dados: ' . $e->getMessage(), 'error');
                  return null;
            }
      }

      public function insertProduct(Product $product)
      {
            try {
                  $name = $product->getName();
                  $type = $product->getType();
                  $price = $product->getPrice();
                  $quantity = $product->getQuantity();

                  $stmt = $this->conn->prepare('INSERT INTO products (name, type, price, quantity) VALUES (?, ?, ?, ?)');

                  $stmt->bindParam(1, $name, PDO::PARAM_STR);
                  $stmt->bindParam(2, $type, PDO::PARAM_INT);
                  $stmt->bindParam(3, $price, PDO::PARAM_STR);
                  $stmt->bindParam(4, $quantity, PDO::PARAM_INT);

                  if ($stmt->execute()) {
                        FlashMessage::setMessage('Produto inserido com sucesso!', 'success');
                  } else {
                        FlashMessage::setMessage('Houve um erro inesperado ao inserir um novo produto no sistema.', 'error');
                  }
            } catch (PDOException $e) {
                  FlashMessage::setMessage('Erro ao acessar o banco de dados: ' . $e->getMessage(), 'error');
            }
      }

      public function updateProduct($id, Product $product)
      {
            try {

                  $existingProduct = $this->showProduct($id);

                  if (!$existingProduct) {
                        FlashMessage::setMessage('Produto não encontrado no sistema.', 'error');
                        return;
                  }

                  $name = $product->getName();
                  $type = $product->getType();
                  $price = $product->getPrice();
                  $quantity = $product->getQuantity();

                  $stmt = $this->conn->prepare('UPDATE products SET name = ?, type = ?, price = ?, quantity = ? WHERE id = ?');

                  $stmt->bindParam(1, $name, PDO::PARAM_STR);
                  $stmt->bindParam(2, $type, PDO::PARAM_INT);
                  $stmt->bindParam(3, $price, PDO::PARAM_STR);
                  $stmt->bindParam(4, $quantity, PDO::PARAM_INT);
                  $stmt->bindParam(5, $id, PDO::PARAM_INT);

                  if ($stmt->execute()) {
                        FlashMessage::setMessage('Produto atualizado com sucesso!', 'success');
                  } else {
                        FlashMessage::setMessage('Houve um erro ao atualizar o produto.', 'error');
                  }
            } catch (PDOException $e) {
                  FlashMessage::setMessage('Erro ao acessar o banco de dados: ' . $e->getMessage(), 'error');
            }
      }

      public function deleteProduct($id)
      {
            try {
                  $existingProduct = $this->showProduct($id);

                  if (!$existingProduct) {
                        FlashMessage::setMessage('Produto não encontrado no sistema.', 'error');
                  }

                  $stmt = $this->conn->prepare('DELETE FROM products WHERE id = ?');
                  $stmt->bindParam(1, $id, PDO::PARAM_INT);
                  if ($stmt->execute()) {
                        FlashMessage::setMessage('Produto deletado com sucesso', 'success');
                  } else {
                        FlashMessage::setMessage('Houve um erro inesperado ao deletar o produto', 'error');
                  }
            } catch (PDOException $e) {
                  FlashMessage::setMessage('Erro ao acessar o banco de dados: ' . $e->getMessage(), 'error');
            }
      }
}

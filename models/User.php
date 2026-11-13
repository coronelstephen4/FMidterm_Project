<?php
class User {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getByUsername(string $username): ?array {
        $sql = "SELECT u.user_id, u.username, u.password_hash, r.role_name AS role
                FROM public.users u
                JOIN public.roles r ON u.role_id = r.role_id
                WHERE u.username = :username";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(["username" => $username]);

        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function getById(int $user_id): ?array {
        $sql = "SELECT u.user_id, u.username, r.role_name AS role, u.created_at
                FROM public.users u
                JOIN public.roles r ON u.role_id = r.role_id
                WHERE u.user_id = :user_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(["user_id" => $user_id]);

        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function create(string $username, string $password, string $role): int {
        if (!in_array($role, ['admin', 'staff'], true)) {
            throw new InvalidArgumentException('Invalid user role.');
        }

        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO public.users (username, password_hash, role_id)
                SELECT :username, :password_hash, role_id
                FROM public.roles
                WHERE role_name = :role
                RETURNING user_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            "username"      => $username,
            "password_hash" => $password_hash,
            "role"          => $role
        ]);

        return (int) $stmt->fetchColumn();
    }

    public function updatePassword(int $user_id, string $password): bool {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $sql = "UPDATE public.users
                SET password_hash = :password_hash
                WHERE user_id = :user_id";
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            "password_hash" => $password_hash,
            "user_id"       => $user_id
        ]);
    }
}

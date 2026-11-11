<?php
class Farmer {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getAll(
        string $search = '',
        string $address = '',
        string $sort = 'farmer_id',
        string $direction = 'ASC',
        int $limit = 10,
        int $offset = 0
    ): array {
        $sortColumns = [
            'farmer_id' => 'farmer_id',
            'first_name' => 'first_name',
            'last_name' => 'last_name',
            'address' => 'address',
            'date_registered' => 'date_registered'
        ];
        $sortColumn = $sortColumns[$sort] ?? 'farmer_id';
        $sortDirection = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';

        $sql = "SELECT farmer_id, first_name, last_name, contact_number, address, date_registered
                FROM farmer
                WHERE 1 = 1";
        $params = [];

        if ($search !== '') {
            $sql .= " AND (
                first_name ILIKE :search_first_name
                OR last_name ILIKE :search_last_name
                OR contact_number ILIKE :search_contact
            )";
            $searchValue = '%' . $search . '%';
            $params['search_first_name'] = $searchValue;
            $params['search_last_name'] = $searchValue;
            $params['search_contact'] = $searchValue;
        }

        if ($address !== '') {
            $sql .= ' AND address ILIKE :address';
            $params['address'] = '%' . $address . '%';
        }

        $sql .= " ORDER BY {$sortColumn} {$sortDirection}, farmer_id ASC
                  LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value, PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        $stmt->bindValue(':offset', max(0, $offset), PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function countAll(string $search = '', string $address = ''): int {
        $sql = "SELECT COUNT(*)
                FROM farmer
                WHERE 1 = 1";
        $params = [];

        if ($search !== '') {
            $sql .= " AND (
                first_name ILIKE :search_first_name
                OR last_name ILIKE :search_last_name
                OR contact_number ILIKE :search_contact
            )";
            $searchValue = '%' . $search . '%';
            $params['search_first_name'] = $searchValue;
            $params['search_last_name'] = $searchValue;
            $params['search_contact'] = $searchValue;
        }

        if ($address !== '') {
            $sql .= ' AND address ILIKE :address';
            $params['address'] = '%' . $address . '%';
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }

    public function getById(int $farmer_id): ?array {
        $sql = "SELECT farmer_id, first_name, last_name, contact_number, address, date_registered,
                       created_at, updated_at
                FROM farmer
                WHERE farmer_id = :farmer_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(["farmer_id" => $farmer_id]);

        $farmer = $stmt->fetch();
        return $farmer ?: null;
    }

    public function hasDuplicate(
        string $first_name,
        string $last_name,
        string $contact_number,
        string $address,
        ?int $exclude_farmer_id = null
    ): bool {
        // Flag likely duplicate people even when a name or registration date
        // was entered differently. A shared contact and address is a review
        // candidate; matching full name and address is also a likely duplicate.
        $sql = "SELECT EXISTS (
                    SELECT 1 FROM farmer
                    WHERE (
                        (regexp_replace(BTRIM(contact_number), '\\D', '', 'g') =
                         regexp_replace(BTRIM(:candidate_contact), '\\D', '', 'g')
                         AND LOWER(REGEXP_REPLACE(BTRIM(address), '\\s+', ' ', 'g')) =
                             LOWER(REGEXP_REPLACE(BTRIM(:candidate_address_contact), '\\s+', ' ', 'g')))
                        OR
                        (LOWER(BTRIM(first_name)) = LOWER(BTRIM(:first_name))
                         AND LOWER(BTRIM(last_name)) = LOWER(BTRIM(:last_name))
                         AND LOWER(REGEXP_REPLACE(BTRIM(address), '\\s+', ' ', 'g')) =
                             LOWER(REGEXP_REPLACE(BTRIM(:candidate_address_name), '\\s+', ' ', 'g')))
                    )";
        if ($exclude_farmer_id !== null) {
            $sql .= ' AND farmer_id <> :exclude_farmer_id';
        }
        $sql .= ')';

        $stmt = $this->pdo->prepare($sql);
        $params = [
            'first_name' => $first_name,
            'last_name' => $last_name,
            'candidate_contact' => $contact_number,
            'candidate_address_contact' => $address,
            'candidate_address_name' => $address
        ];
        if ($exclude_farmer_id !== null) {
            $params['exclude_farmer_id'] = $exclude_farmer_id;
        }
        $stmt->execute($params);

        $exists = $stmt->fetchColumn();
        return $exists === true || $exists === 't' || $exists === '1';
    }

    public function create(
        string $first_name,
        string $last_name,
        string $contact_number,
        string $address,
        string $date_registered
    ): int {
        $sql = "INSERT INTO farmer (first_name, last_name, contact_number, address, date_registered)
                VALUES (:first_name, :last_name, :contact_number, :address, :date_registered)
                RETURNING farmer_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            "first_name" => $first_name,
            "last_name" => $last_name,
            "contact_number" => $contact_number,
            "address" => $address,
            "date_registered" => $date_registered
        ]);

        return (int) $stmt->fetchColumn();
    }

    public function update(
        int $farmer_id,
        string $first_name,
        string $last_name,
        string $contact_number,
        string $address,
        string $date_registered
    ): bool {
        $sql = "UPDATE farmer
                SET first_name = :first_name,
                    last_name = :last_name,
                    contact_number = :contact_number,
                    address = :address,
                    date_registered = :date_registered,
                    updated_at = CURRENT_TIMESTAMP
                WHERE farmer_id = :farmer_id";
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            "first_name" => $first_name,
            "last_name" => $last_name,
            "contact_number" => $contact_number,
            "address" => $address,
            "date_registered" => $date_registered,
            "farmer_id" => $farmer_id
        ]);
    }

    public function delete(int $farmer_id): bool {
        $sql = "DELETE FROM farmer WHERE farmer_id = :farmer_id";
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute(["farmer_id" => $farmer_id]);
    }
}

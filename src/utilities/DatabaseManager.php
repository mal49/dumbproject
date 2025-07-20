<?php
/**
 * DatabaseManager - Database abstraction layer
 * Simplifies common database operations and queries
 */
class DatabaseManager
{
    private $pdo;

    /**
     * Gets the student details by ID
     */
    public function getStudentDetails($studentId) {
        $query = "SELECT 
            Student_id,
            Name as name,
            Faculty_code as faculty_code,
            Campus as campus,
            Gender as gender,
            Level_of_study as level_of_study,
            Mode_of_study as mode_of_study,
            mailing_address,
            Postcode as postcode,
            mobile_phone_no,
            email
        FROM student WHERE Student_id = ?";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$studentId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Updates student profile information
     */
    public function updateStudentProfile($studentId, $data) {
        $query = "UPDATE student SET 
            Name = ?,
            Faculty_code = ?,
            Campus = ?,
            mailing_address = ?,
            Postcode = ?,
            mobile_phone_no = ?,
            email = ?
        WHERE Student_id = ?";
        $stmt = $this->pdo->prepare($query);
        return $stmt->execute([
            $data['name'],
            $data['faculty_code'],
            $data['campus'],
            $data['mailing_address'],
            $data['postcode'],
            $data['mobile_phone_no'],
            $data['email'],
            $studentId
        ]);
    }

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Student-related queries
     */
    public function getStudentRegisteredCourses($studentId)
    {
        $stmt = $this->pdo->prepare("
            SELECT DISTINCT c.course_code, c.course_name, c.credit_hour, ca.is_repeat
            FROM add_drop_application ada 
            JOIN course_add ca ON ada.application_id = ca.application_id 
            JOIN course c ON ca.course_code = c.course_code
            WHERE ada.student_id = ?
            ORDER BY c.course_code
        ");
        $stmt->execute([$studentId]);
        return $stmt->fetchAll();
    }

    public function getStudentCurrentCredits($studentId)
    {
        $stmt = $this->pdo->prepare("
            SELECT COALESCE(SUM(c.credit_hour), 0) as total_credits
            FROM add_drop_application ada
            JOIN course_add ca ON ada.application_id = ca.application_id
            JOIN course c ON ca.course_code = c.course_code
            WHERE ada.student_id = ?
        ");
        $stmt->execute([$studentId]);
        $result = $stmt->fetch();
        return $result['total_credits'] ?? 0;
    }

    public function getStudentPendingDropRequests($studentId)
    {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) as pending_drops 
            FROM add_drop_application ada 
            JOIN course_drop cd ON ada.application_id = cd.application_id 
            WHERE ada.student_id = ?
        ");
        $stmt->execute([$studentId]);
        $result = $stmt->fetch();
        return $result['pending_drops'] ?? 0;
    }

    public function getStudentDropRequests($studentId)
    {
        $stmt = $this->pdo->prepare("
            SELECT cd.course_code, c.course_name, cd.Reasons, cd.lecturer_id, 
                   l.lecturer_name, ada.application_date,
                   CASE 
                       WHEN cd.status IS NULL THEN 'Pending'
                       ELSE cd.status 
                   END as status
            FROM add_drop_application ada 
            JOIN course_drop cd ON ada.application_id = cd.application_id 
            JOIN course c ON cd.course_code = c.course_code
            LEFT JOIN lecturer l ON cd.lecturer_id = l.lecturer_id
            WHERE ada.student_id = ?
            ORDER BY ada.application_date DESC
        ");
        $stmt->execute([$studentId]);
        return $stmt->fetchAll();
    }

    /**
     * Course-related queries
     */
    public function getAllCourses()
    {
        $stmt = $this->pdo->query("SELECT * FROM course ORDER BY course_code");
        return $stmt->fetchAll();
    }

    public function getCourseCount()
    {
        $stmt = $this->pdo->query("SELECT COUNT(*) as course_count FROM course");
        return $stmt->fetch()['course_count'];
    }

    public function getCoursePrerequisites($courseCode, $studentId)
    {
        $stmt = $this->pdo->prepare("
            SELECT cp.prerequisite as prerequisite_code, c.course_name,
                   CASE WHEN pc.course_code IS NOT NULL THEN 'Completed' ELSE 'Not Completed' END as status
            FROM course_prerequisite cp
            JOIN course c ON cp.prerequisite = c.course_code
            LEFT JOIN passed_courses pc ON cp.prerequisite = pc.course_code AND pc.student_id = ?
            WHERE cp.course_code = ?
        ");
        $stmt->execute([$studentId, $courseCode]);
        return $stmt->fetchAll();
    }

    public function getMissingPrerequisites($courseCode, $studentId)
    {
        $stmt = $this->pdo->prepare("
            SELECT cp.prerequisite as prerequisite_code, c.course_name 
            FROM course_prerequisite cp
            JOIN course c ON cp.prerequisite = c.course_code
            WHERE cp.course_code = ?
            AND cp.prerequisite NOT IN (
                SELECT course_code FROM passed_courses WHERE student_id = ?
            )
        ");
        $stmt->execute([$courseCode, $studentId]);
        return $stmt->fetchAll();
    }

    /**
     * Application management
     */
    public function createApplication($studentId)
    {
        $applicationId = 'APP' . time() . rand(100, 999);
        $stmt = $this->pdo->prepare("
            INSERT INTO add_drop_application (application_id, student_id, application_date) 
            VALUES (?, ?, CURDATE())
        ");
        $stmt->execute([$applicationId, $studentId]);
        return $applicationId;
    }

    public function addCourseToApplication($applicationId, $courseCode, $isRepeat = 0)
    {
        $addId = 'ADD' . time() . rand(100, 999);
        $stmt = $this->pdo->prepare("
            INSERT INTO course_add (add_id, application_id, course_code, is_repeat) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$addId, $applicationId, $courseCode, $isRepeat]);
        return $addId;
    }

    public function addDropRequest($applicationId, $courseCode, $reason, $lecturerId)
    {
        $dropId = 'DROP' . time() . rand(100, 999);
        $stmt = $this->pdo->prepare("
            INSERT INTO course_drop (drop_id, application_id, course_code, Reasons, lecturer_id) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$dropId, $applicationId, $courseCode, $reason, $lecturerId]);
        return $dropId;
    }

    /**
     * Lecturer-related queries
     */
    public function getLecturerDropRequests($lecturerId = null)
    {
        $sql = "
            SELECT cd.drop_id, cd.course_code, c.course_name, cd.Reasons,
                   ada.student_id, s.Name as student_name, ada.application_date,
                   cd.lecturer_id, l.lecturer_name,
                   CASE 
                       WHEN cd.status IS NULL THEN 'Pending'
                       ELSE cd.status 
                   END as status
            FROM course_drop cd
            JOIN add_drop_application ada ON cd.application_id = ada.application_id
            JOIN student s ON ada.student_id = s.Student_id
            JOIN course c ON cd.course_code = c.course_code
            LEFT JOIN lecturer l ON cd.lecturer_id = l.lecturer_id
        ";

        if ($lecturerId) {
            $sql .= " WHERE cd.lecturer_id = ?";
            $stmt = $this->pdo->prepare($sql . " ORDER BY ada.application_date DESC");
            $stmt->execute([$lecturerId]);
        } else {
            $stmt = $this->pdo->prepare($sql . " ORDER BY ada.application_date DESC");
            $stmt->execute();
        }

        return $stmt->fetchAll();
    }

    public function getAllStudents()
    {
        $stmt = $this->pdo->query("SELECT Student_id, Name, email FROM student ORDER BY Student_id");
        return $stmt->fetchAll();
    }

    /**
     * Utility methods
     */
    public function getNextStudentId()
    {
        $nextStudentId = "ST001";
        try {
            $stmt = $this->pdo->query("SELECT Student_id FROM student ORDER BY Student_id DESC LIMIT 1");
            $result = $stmt->fetch();
            if ($result) {
                $lastNumber = intval(substr($result['Student_id'], 2));
                $nextNumber = $lastNumber + 1;
                $nextStudentId = 'ST' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
            }
        } catch (PDOException $e) {
            // Keep default if error
        }
        return $nextStudentId;
    }

    public function deleteStudent($studentId)
    {
        $stmt = $this->pdo->prepare("DELETE FROM student WHERE Student_id = ?");
        return $stmt->execute([$studentId]);
    }

    public function studentExists($studentId)
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM student WHERE Student_id = ?");
        $stmt->execute([$studentId]);
        return $stmt->fetch()['count'] > 0;
    }
}
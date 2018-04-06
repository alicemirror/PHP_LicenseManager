  <?php
  
  /**
  * Helper for db queries management for the Licensing database
  *
  * DbHelper class contains methods for table management and generic
  * queries for insert/update/remove records in a format independent from the
  * field type. DbHelper class uses PDO data object API for best database
  * performances and responses.
  */
  Class DbHelper {

	const EXCEPT_NO_ROWS ="No updated rows";
	const EXCEPT_QUERY_ERROR = "Query error";
	const EXCEPT_NOT_FOUND = "Not found";
	
	/**
	* Insert or update a record. If the primary_key exists the record is updated.
	*  
	* When the record already exist and should be updated with the method parameters it is 
	* needed the primary_key already defined. The function can return 'false' or a valid ID.
	* 
	* @param $array The record array
	* @param $table The target table
	* @param $dbh The database instance
	* @param $update_array The update record
	* @param $primary_key The unique primary key for already existing records
	*/
	static function insertOrUpdate(	$array, $table, 
									$dbh, $update_array = null, 
									$primary_key = null) {
	
		$set = '';
	
		// Extract the array in the ins[] vector
		foreach ($array as $field=> $v)
			$ins[] = ' :' . $field;
		
		if($update_array == null) {
			foreach ($array as $field => $v)
				$set  .= " $field = " . ' :' . $field . ",";
		}
		else {
			foreach ($update_array as $field => $v)
				$set  .= " $field = " . ' :' . $field . ",";
		}
		
		$set = rtrim($set, ',');
		$ins    = implode(',', $ins);
		$fields = implode(',', array_keys($array));
		
		if( isset($primary_key) ) {
			$zzz = "$primary_key=LAST_INSERT_ID($primary_key),";
		}
		else
			$zzz = "";
		
		$sql = "INSERT INTO "
				. $table . "("
				. $fields . ") VALUES ("
				. $ins . ") ON DUPLICATE KEY UPDATE "
				. $zzz . " "
				. $set;
		
		try{
			$sth = $dbh->prepare($sql);
			foreach ($array as $f => $v)
				$sth->bindValue(':' . $f, $v);
			
			$res = $sth->execute();
		} catch(PDOException $e) {
			Dbg::e("$e");
			return false;
		}
		
		if($res === false) {
			return false;
		}
		else {
			// return a number != 0 only if the index is auto increment
			return $dbh->lastInsertId();
		}
		
	} // insert_or_update

	/**
	* Insert a data array in a table
	*
	* Recursive insertion of the data array in a table of the database
	* Due that the array will represend the fields values the insertion will refer
	* to a record.
	*
	* @param $array The data array for insertion
	* @param $table The table where data are to be inserted
	* @param $dbh The database reference
	* @throw PDOException
	*/
	function insert($array, $table, $dbh){
		// Extract the array in the ins[] vector
		foreach ($array as $field=> $v)
			$ins[] = ' :' . $field;

		// Compact the elements in a single string for both field names
		// and field data. Both values are structured as comma separated elements.
		$ins    = implode(',', $ins);
		$fields = implode(',', array_keys($array));
		
		// Create the query string
		$sql = 	"INSERT INTO " . 
					$table . " (" . 
					$fields . 
					") VALUES (" . 
					$ins . ")";
		  
		Dbg::d("$sql");
		  
		$res = false;

		try{
			// Prepare the query
			$sth = $dbh->prepare($sql);
			// Associates the field-value couple for every record field
			foreach ($array as $f => $v)
				$sth->bindValue(':' . $f, $v);
			
			// execute the query
			$res = $sth->execute();
			
			if($res === false ) {
				throw new PDOException(EXCEPT_NO_ROWS);
			}
		}
		catch(PDOException $e) {
			Dbg::e("$e");
			return false;
		}
	return $dbh->lastInsertId();

	}

	/**
	* Database table update
	* 
	* Update table with the array defining the record fields in the
	* dbh referenced database.
	* 
	* @note The query is done in two steps:
	*
	* First, str is built with the table field names assigned to themselves.
	* Second, bind function is used to replace the second field name with the 
	* corresponding data value to be set in the record. This method avoid the sql 
	* injection and grant a more stable query execution.
	* 
	* @param $array The data array for update
	* @param $table The table whose record should be updated
	* @param $dbh The referenced database
	* @param $wherea
	* @param $whereb
	* @throws PDOException
	*/
	static function update($array, $table, $dbh, $wherea, $whereb) {
		  
		$set = '';
		
		// In the loop $v is used as dummy variable to extrack the key that
		// in this first phase is simply trashed.
		foreach ($array as $field => $v) {
			$set .= " " . $field . " = " . ' :' . $field. ",";
		//echo ('\n=> DbHelper->update() - ' . $set);
		} // first loop
		
		// Remove the last redundant comma character.
		$set = rtrim($set, ',');
		
		// Create the query string
		$sql = "UPDATE " . $table . " SET " . $set . " WHERE  " . $wherea . " = :" . $wherea;
		
		//echo('\n=> DbHelper->update()RJS sql on token request = ' . $sql); 
		
		//Dbg::d("$sql");
		
		try {
			$sth = $dbh->prepare($sql);
			foreach ($array as $f => $v) {
				$sth->bindValue(':' . $f, $v);
				// echo(' EM - $f = ' . $f . '- $v = ' . $v . " ");
				// The binding applies the second field name with data substitution.
				$sth->bindValue(':' . $wherea, $whereb);                    
				//echo(' RJS - wherea = ' . $wherea . '; RJS - whereb = ' . $whereb . ": ");
			} // variables loop
			
			$res = $sth->execute();
			$count = $sth->rowCount();
		
			// Dbg::d("updatedRows:" . $count);
			// em - Modified the control from'=== false' to != 1
			if($res != 1 )
			throw new PDOException(EXCEPT_NO_ROWS);
		} // try 
		catch(PDOException $e) {
			Dbg::e("$e");
			return false;
		} // catch

	return $count;
	}
		
	  /**
	  * Find the record with the requested ID field.
	  * 
	  * @note renamed variable \c $required_uniq_fileds to $required_uniq_fields
	  * 
	  * @param $array The array of the full record in the form field_name - field_data
	  * @param $required_uniq_fields The key fields or fields that should be non-replicated
	  * @param $dbh The database instance
	  * @param $id_column The column containing the searched ID
	  * @param $table_name The table for the search
	  */
	  static function findId($array, $required_uniq_fields, $dbh, $id_column, $table_name){
		  
		  // Loop creating the set with the minimum required fields
		  foreach ($required_uniq_fields as $field){
			  $set .= $field . " = '" . $array[$field] . "' OR ";
		  }
		  
		  // Remove the last redundant 'OR'
		  $set = rtrim($set, 'OR ');
		  // Remove the last redundant ','
		  $column_list = rtrim( $column_list, ', ');
		  
		  // Create the SQL Select 
		  $sql="SELECT " . $id_column . " FROM "
		  . $table_name . " WHERE "
		  . $set;
		  //echo('rjs - sql = ' . $sql);
		  
		  // Query execution 
		  try{
			  // Prepare the SQL query
			  $sth = $dbh->prepare($sql);
			  
			  $res = $sth->execute();
			  
			  if($res != 1 ) {
				  throw new PDOException(EXCEPT_QUERY_ERROR);
			  }
			  
			  $count = $sth->rowCount();
			  if( $count<1 ) {
				  throw new PDOException(EXCEPT_NOT_FOUND);
			  }
			  
			  $result = $sth->fetch(PDO::FETCH_ASSOC);
			  $sth->closeCursor();
		  }
		  catch(PDOException $e){
			  print_r($e);
			  return false;
		  }
		  
		  return $result[$id_column];
	  }
	  
  } // end of class
  
  ?>
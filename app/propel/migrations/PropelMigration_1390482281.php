<?php

/**
 * Data object containing the SQL and PHP code to migrate the database
 * up to version 1390482281.
 * Generated on 2014-01-23 14:04:41 by macbookdata
 */
class PropelMigration_1390482281
{

    public function preUp($manager)
    {
        // add the pre-migration code here
    }

    public function postUp($manager)
    {
        // add the post-migration code here
    }

    public function preDown($manager)
    {
        // add the pre-migration code here
    }

    public function postDown($manager)
    {
        // add the post-migration code here
    }

    /**
     * Get the SQL statements for the Up migration
     *
     * @return array list of the SQL strings to execute for the Up migration
     *               the keys being the datasources
     */
    public function getUpSQL()
    {
        return array (
  'dtametadata' => '
ALTER TABLE "publication" ADD "firstpage" INTEGER;
',
);
    }

    /**
     * Get the SQL statements for the Down migration
     *
     * @return array list of the SQL strings to execute for the Down migration
     *               the keys being the datasources
     */
    public function getDownSQL()
    {
        return array (
  'dtametadata' => '
ALTER TABLE "publication" DROP COLUMN "firstpage";
',
);
    }

}
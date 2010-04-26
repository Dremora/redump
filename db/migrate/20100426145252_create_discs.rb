class CreateDiscs < ActiveRecord::Migration
  def self.up
    create_table :discs do |t|
      t.text :comments
      t.date :internal_date
      t.string :internal_serial
      t.integer :status, :limit => 1
      t.string :title
      t.string :subtitle
      t.string :scene_filename
      t.string :filename
      t.integer :size
      t.string :crc32, :limit => 8
      t.string :md5, :limit => 32
      t.string :sha1, :limit => 40

      t.timestamps
    end
  end

  def self.down
    drop_table :discs
  end
end

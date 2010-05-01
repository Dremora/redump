class AddNotNullToDiscFields < ActiveRecord::Migration
  def self.up
    change_column :discs, :comments, :text, :null => false
    change_column :discs, :internal_date, :string, :limit => 10, :null => false
    change_column :discs, :internal_serial, :string, :null => false
    change_column :discs, :status, :integer, :null => false, :limit => 1
    change_column :discs, :title, :string, :null => false
    change_column :discs, :subtitle, :string, :null => false
    change_column :discs, :scene_filename, :string, :null => false
    change_column :discs, :filename, :string, :null => false
    change_column :discs, :crc32, :string, :null => false, :limit => 8
    change_column :discs, :md5, :string, :null => false, :limit => 32
    change_column :discs, :sha1, :string, :null => false, :limit => 40
  end

  def self.down
    change_column :discs, :comments, :text
    change_column :discs, :internal_date, :string, :limit => 10
    change_column :discs, :internal_serial, :string
    change_column :discs, :status, :integer, :limit => 1
    change_column :discs, :title, :string
    change_column :discs, :subtitle, :string
    change_column :discs, :scene_filename, :string
    change_column :discs, :filename, :string
    change_column :discs, :crc32, :string, :limit => 8
    change_column :discs, :md5, :string, :limit => 32
    change_column :discs, :sha1, :string, :limit => 40
  end
end

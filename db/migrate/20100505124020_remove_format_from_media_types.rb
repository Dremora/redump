class RemoveFormatFromMediaTypes < ActiveRecord::Migration
  def self.up
    remove_column :media_types, :format
  end

  def self.down
    add_column :media_types, :format, :string, :null => false
  end
end

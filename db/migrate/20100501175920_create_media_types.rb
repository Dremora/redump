class CreateMediaTypes < ActiveRecord::Migration
  def self.up
    create_table :media_types do |t|
      t.string :title, :null => false
      t.string :abbreviation, :null => false
      t.string :format, :null => false
      t.references :parent

      t.timestamps
    end
  end

  def self.down
    drop_table :media_types
  end
end

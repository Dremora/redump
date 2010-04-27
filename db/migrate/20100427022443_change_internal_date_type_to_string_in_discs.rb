class ChangeInternalDateTypeToStringInDiscs < ActiveRecord::Migration
  def self.up
    change_column :discs, :internal_date, :string, :limit => 10
  end

  def self.down
    change_column :discs, :internal_date, :date
  end
end
